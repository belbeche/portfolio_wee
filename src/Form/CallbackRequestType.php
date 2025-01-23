<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CallbackRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'callback_request.name.label',
                'attr' => [
                    'placeholder' => 'callback_request.name.placeholder',
                ],
            ])
            ->add('phonePrefix', ChoiceType::class, [
                'label' => 'callback_request.phone_prefix.label',
                'choices' => [
                    '+1 (USA/Canada)' => '+1',
                    '+7 (Russie)' => '+7',
                    '+20 (Égypte)' => '+20',
                    '+27 (Afrique du Sud)' => '+27',
                    '+30 (Grèce)' => '+30',
                    '+31 (Pays-Bas)' => '+31',
                    '+32 (Belgique)' => '+32',
                    '+33 (France)' => '+33',
                    '+34 (Espagne)' => '+34',
                    '+39 (Italie)' => '+39',
                    '+40 (Roumanie)' => '+40',
                    '+41 (Suisse)' => '+41',
                    '+44 (Royaume-Uni)' => '+44',
                    '+45 (Danemark)' => '+45',
                    '+46 (Suède)' => '+46',
                    '+47 (Norvège)' => '+47',
                    '+48 (Pologne)' => '+48',
                    '+49 (Allemagne)' => '+49',
                    '+51 (Pérou)' => '+51',
                    '+52 (Mexique)' => '+52',
                    '+53 (Cuba)' => '+53',
                    '+54 (Argentine)' => '+54',
                    '+55 (Brésil)' => '+55',
                    '+56 (Chili)' => '+56',
                    '+57 (Colombie)' => '+57',
                    '+58 (Venezuela)' => '+58',
                    '+60 (Malaisie)' => '+60',
                    '+61 (Australie)' => '+61',
                    '+62 (Indonésie)' => '+62',
                    '+63 (Philippines)' => '+63',
                    '+64 (Nouvelle-Zélande)' => '+64',
                    '+65 (Singapour)' => '+65',
                    '+66 (Thaïlande)' => '+66',
                    '+81 (Japon)' => '+81',
                    '+82 (Corée du Sud)' => '+82',
                    '+84 (Vietnam)' => '+84',
                    '+86 (Chine)' => '+86',
                    '+90 (Turquie)' => '+90',
                    '+91 (Inde)' => '+91',
                    '+92 (Pakistan)' => '+92',
                    '+93 (Afghanistan)' => '+93',
                    '+94 (Sri Lanka)' => '+94',
                    '+95 (Myanmar)' => '+95',
                    '+98 (Iran)' => '+98',
                    '+212 (Maroc)' => '+212',
                    '+213 (Algérie)' => '+213',
                    '+216 (Tunisie)' => '+216',
                    '+218 (Libye)' => '+218',
                    '+220 (Gambie)' => '+220',
                    '+221 (Sénégal)' => '+221',
                    '+222 (Mauritanie)' => '+222',
                    '+223 (Mali)' => '+223',
                    '+224 (Guinée)' => '+224',
                    '+225 (Côte d’Ivoire)' => '+225',
                    '+226 (Burkina Faso)' => '+226',
                    '+227 (Niger)' => '+227',
                    '+228 (Togo)' => '+228',
                    '+229 (Bénin)' => '+229',
                    '+230 (Maurice)' => '+230',
                    '+231 (Libéria)' => '+231',
                    '+232 (Sierra Leone)' => '+232',
                    '+233 (Ghana)' => '+233',
                    '+234 (Nigeria)' => '+234',
                    '+235 (Tchad)' => '+235',
                    '+236 (République centrafricaine)' => '+236',
                    '+237 (Cameroun)' => '+237',
                    '+238 (Cap-Vert)' => '+238',
                    '+239 (Sao Tomé-et-Principe)' => '+239',
                    '+240 (Guinée équatoriale)' => '+240',
                    '+241 (Gabon)' => '+241',
                    '+242 (Congo)' => '+242',
                    '+243 (RD Congo)' => '+243',
                    '+244 (Angola)' => '+244',
                    '+245 (Guinée-Bissau)' => '+245',
                    '+248 (Seychelles)' => '+248',
                    '+250 (Rwanda)' => '+250',
                    '+251 (Éthiopie)' => '+251',
                    '+252 (Somalie)' => '+252',
                    '+253 (Djibouti)' => '+253',
                    '+254 (Kenya)' => '+254',
                    '+255 (Tanzanie)' => '+255',
                    '+256 (Ouganda)' => '+256',
                    '+257 (Burundi)' => '+257',
                    '+258 (Mozambique)' => '+258',
                    '+260 (Zambie)' => '+260',
                    '+261 (Madagascar)' => '+261',
                    '+262 (Réunion)' => '+262',
                    '+263 (Zimbabwe)' => '+263',
                    '+264 (Namibie)' => '+264',
                    '+265 (Malawi)' => '+265',
                    '+266 (Lesotho)' => '+266',
                    '+267 (Botswana)' => '+267',
                    '+268 (Eswatini)' => '+268',
                    '+269 (Comores)' => '+269',
                    '+290 (Sainte-Hélène)' => '+290',
                    '+291 (Érythrée)' => '+291',
                    '+297 (Aruba)' => '+297',
                    '+298 (Îles Féroé)' => '+298',
                    '+299 (Groenland)' => '+299',
                    '+350 (Gibraltar)' => '+350',
                    '+351 (Portugal)' => '+351',
                    '+352 (Luxembourg)' => '+352',
                    '+353 (Irlande)' => '+353',
                    '+354 (Islande)' => '+354',
                    '+355 (Albanie)' => '+355',
                    '+356 (Malte)' => '+356',
                    '+357 (Chypre)' => '+357',
                    '+358 (Finlande)' => '+358',
                    '+359 (Bulgarie)' => '+359',
                    '+370 (Lituanie)' => '+370',
                    '+371 (Lettonie)' => '+371',
                    '+372 (Estonie)' => '+372',
                    '+373 (Moldavie)' => '+373',
                    '+374 (Arménie)' => '+374',
                    '+375 (Biélorussie)' => '+375',
                    '+376 (Andorre)' => '+376',
                    '+377 (Monaco)' => '+377',
                    '+378 (Saint-Marin)' => '+378',
                    '+380 (Ukraine)' => '+380',
                    '+381 (Serbie)' => '+381',
                    '+382 (Monténégro)' => '+382',
                    '+383 (Kosovo)' => '+383',
                    '+385 (Croatie)' => '+385',
                    '+386 (Slovénie)' => '+386',
                    '+387 (Bosnie-Herzégovine)' => '+387',
                    '+389 (Macédoine du Nord)' => '+389',
                    '+420 (République tchèque)' => '+420',
                    '+421 (Slovaquie)' => '+421',
                    '+423 (Liechtenstein)' => '+423',
                ],
                'placeholder' => 'callback_request.phone_prefix.placeholder',
            ])
            ->add('phone', TelType::class, [
                'label' => 'callback_request.phone.label',
                'attr' => [
                    'placeholder' => 'callback_request.phone.placeholder',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'callback_request.email.label',
                'attr' => [
                    'placeholder' => 'callback_request.email.placeholder',
                ],
            ]);
    }
}
