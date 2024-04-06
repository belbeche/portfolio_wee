<?php

namespace App\Repository;

use App\Entity\Authlog;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Authlog>
 *
 * @method Authlog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Authlog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Authlog[]    findAll()
 * @method Authlog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Authlog::class);
    }

    public const BLACK_LISTING_DELAY_IN_MINUTES = 15;

    public const MAX_FAILED_AUTH_ATTEMPTS = 5;

    /**
     * Adds a failed authentiication attempt.
     *
     * @param string $emailEntered
     * @param string|null $userIP
     * @param boolean $isBlackListed Set to true if the email/userIP pair must be blacklisted.
     * @return void
     */
    public function addFailedAuthAttempt(
        string $emailEntered,
        ?string $userIP,
        bool $isBlackListed = false
    ): void {
        // This is not a successful connection temptation
        $authAttempt = (new Authlog($emailEntered, $userIP))->setIsSuccessfulAuth(false);


        // if isBlackListed == true
        if ($isBlackListed) {
            $authAttempt->setStartOfBlackListing(new \DateTimeImmutable('now'))
                ->setEndOfBlackListing(new \DateTimeImmutable(sprintf('+%d minutes', self::BLACK_LISTING_DELAY_IN_MINUTES)));
        }

        $this->_em->persist($authAttempt);

        $this->_em->flush();
    }
    /**
     * Adds a successful authentication attempt.
     *
     * @param string $emailEntered
     * @param string|null $userIP
     * @param boolean $isRememberMeAuth Set to true if the user is authenticated by remember me cookie.
     * @return void
     */
    public function addSuccessfulAuthAttempt(
        string $emailEntered,
        ?string $userIP,
        bool $isRememberMeAuth = false
    ): void {
        $authAttempt = new AuthLog($emailEntered, $userIP);

        $authAttempt->setIsSuccessfulAuth(true)
            ->setIsRememberMeAuth($isRememberMeAuth);

        $this->_em->persist($authAttempt);
        $this->_em->flush();
    }
    /**
     * Returns the number of recent failed authentication attempts.
     *
     * @param string $emailEntered
     * @param String|null $userIP
     * @return integer
     */
    public function getRecentAuthAttemptFailure(
        string $emailEntered,
        ?String $userIP
    ): int {
        return $this->createQueryBuilder('af')
            ->select('COUNT(af)')
            ->where('af.authAttemptAt >= :datetime')
            ->andWhere('af.userIP = :userIP')
            ->andWhere('af.emailEntered = :email_entered')
            ->andWhere('af.isSuccessfulAuth = false')
            ->setParameters([
                'datetime' => new \DateTimeImmutable(sprintf('-%d minutes', self::BLACK_LISTING_DELAY_IN_MINUTES)),
                'email_entered' => $emailEntered,
                'user_ip' => $userIP
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
    /**
     * Returns whether or not the user will be blacklistend on the next failed attempt.
     *
     * @param string $emailEntered
     * @param string|null $userIP
     * @return boolean
     */
    public function isBlackListedWithThisAttemptFaillure(
        string $emailEntered,
        ?string $userIP
    ): bool {
        return $this->getRecentAuthAttemptFailure($emailEntered, $userIP) >= self::MAX_FAILED_AUTH_ATTEMPTS - 2;
    }

    /**
     * Returns the last entry in the blacklist of an email/userIP pair if it exists.
     *
     * @param string $emailEntered
     * @param string|null $userIP
     * @return Authlog|null
     */
    public function getEmailAndUserIpPairBlackListedIfExists(
        string $emailEntered,
        ?string $userIP
    ): ?Authlog {
        return $this->createQueryBuilder('bl')
            ->select('bl')
            ->where('bl.userIP = :user_IP')
            ->andWhere('bl.emailEntered = :email_entered')
            ->andWhere('bl.endOfBlackListing IS NOT NULL')
            ->andWhere('bl.endOfBlackListing >= :datetime')
            ->setParameters([
                'datetime' => new \DateTimeImmutable(sprintf('-%d minutes', self::BLACK_LISTING_DELAY_IN_MINUTES)),
                'email_entered' => $emailEntered,
                'user_IP' => $userIP
            ])
            ->orderBy('bl.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Returns the end of black-listing rounded up to the next minute.
     *
     * @param string $emailEntered
     * @param string|null $userIP
     * @return string|null The time with format like this: 12h00.
     */
    public function getEndOfBlackListing(
        string $emailEntered,
        ?string $userIP
    ): ?string {
        $blackListing = $this->getEmailAndUserIpPairBlackListedIfExists($emailEntered, $userIP);

        if (!$blackListing || $blackListing->getEndOfBlackListing() === null) {
            return null;
        }

        return $blackListing->getEndOfBlackListing()->add(new \DateInterval("PT1M"))->format('H\hi');
    }
}
