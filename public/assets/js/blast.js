/*! Blast.js (2.0.0): julian.com/research/blast (C) 2015 Julian Shapiro. MIT @license: en.wikipedia.org/wiki/MIT_License */
!function($,e,t,a){var r=function(){if(t.documentMode)return t.documentMode;for(var e=7;e>0;e--){var r=t.createElement("div");if(r.innerHTML="<!--[if IE "+e+"]><span></span><![endif]-->",r.getElementsByTagName("span").length)return r=null,e;r=null}return a}(),n=e.console||{log:function(){},time:function(){}},i="blast",s={latinPunctuation:"â€“â€”â€²â€™'â€œâ€³â€ž\"(Â«.â€¦Â¡Â¿â€²â€™'â€â€³â€œ\")Â».â€¦!?",latinLetters:"\\u0041-\\u005A\\u0061-\\u007A\\u00C0-\\u017F\\u0100-\\u01FF\\u0180-\\u027F"},l={abbreviations:new RegExp("[^"+s.latinLetters+"](e\\.g\\.)|(i\\.e\\.)|(mr\\.)|(mrs\\.)|(ms\\.)|(dr\\.)|(prof\\.)|(esq\\.)|(sr\\.)|(jr\\.)[^"+s.latinLetters+"]","ig"),innerWordPeriod:new RegExp("["+s.latinLetters+"].["+s.latinLetters+"]","ig"),onlyContainsPunctuation:new RegExp("[^"+s.latinPunctuation+"]"),adjoinedPunctuation:new RegExp("^["+s.latinPunctuation+"]+|["+s.latinPunctuation+"]+$","g"),skippedElements:/(script|style|select|textarea)/i,hasPluginClass:new RegExp("(^| )"+i+"( |$)","gi")};$.fn[i]=function(d){function o(e){return e.replace(l.abbreviations,function(e){return e.replace(/\./g,"{{46}}")}).replace(l.innerWordPeriod,function(e){return e.replace(/\./g,"{{46}}")})}function c(e){return e.replace(/{{(\d{1,3})}}/g,function(e,t){return String.fromCharCode(t)})}function u(e,a){var r=t.createElement(a.tag);if(r.className=i,a.customClass&&(r.className+=" "+a.customClass,a.generateIndexID&&(r.id=a.customClass+"-"+f.blastedIndex)),"all"===a.delimiter&&/\s/.test(e.data)&&(r.style.whiteSpace="pre-line"),a.generateValueClass===!0&&!a.search&&("character"===a.delimiter||"word"===a.delimiter)){var n,s=e.data;"word"===a.delimiter&&l.onlyContainsPunctuation.test(s)&&(s=s.replace(l.adjoinedPunctuation,"")),n=i+"-"+a.delimiter.toLowerCase()+"-"+s.toLowerCase(),r.className+=" "+n}return a.aria&&r.setAttribute("aria-hidden","true"),r.appendChild(e.cloneNode(!1)),r}function g(e,t){var a=-1,r=0;if(3===e.nodeType&&e.data.length){if(f.nodeBeginning&&(e.data=t.search||"sentence"!==t.delimiter?c(e.data):o(e.data),f.nodeBeginning=!1),a=e.data.search(h),-1!==a){var n=e.data.match(h),i=n[0],s=n[1]||!1;""===i?a++:s&&s!==i&&(a+=i.indexOf(s),i=s);var d=e.splitText(a);d.splitText(i.length),r=1,t.search||"sentence"!==t.delimiter||(d.data=c(d.data));var p=u(d,t,f.blastedIndex);d.parentNode.replaceChild(p,d),f.wrappers.push(p),f.blastedIndex++}}else if(1===e.nodeType&&e.hasChildNodes()&&!l.skippedElements.test(e.tagName)&&!l.hasPluginClass.test(e.className))for(var m=0;m<e.childNodes.length;m++)f.nodeBeginning=!0,m+=g(e.childNodes[m],t);return r}function p(t,s){s.debug&&n.time("blast reversal");var l=!1;t.removeClass(i+"-root").removeAttr("aria-label").find("."+i).each(function(){var e=$(this);if(e.closest("."+i+"-root").length)l=!0;else{var t=this.parentNode;7>=r&&t.firstChild.nodeName,t.replaceChild(this.firstChild,this),t.normalize()}}),e.Zepto?t.data(i,a):t.removeData(i),s.debug&&(n.log(i+": Reversed Blast"+(t.attr("id")?" on #"+t.attr("id")+".":".")+(l?" Skipped reversal on the children of one or more descendant root elements.":"")),n.timeEnd("blast reversal"))}var m=$.extend({},$.fn[i].defaults,d),h,f={};if(m.search.length&&("string"==typeof m.search||/^\d/.test(parseFloat(m.search))))m.delimiter=m.search.toString().replace(/[-[\]{,}(.)*+?|^$\\\/]/g,"\\$&"),h=new RegExp("(?:^|[^-"+s.latinLetters+"])("+m.delimiter+"('s)?)(?![-"+s.latinLetters+"])","i");else switch("string"==typeof m.delimiter&&(m.delimiter=m.delimiter.toLowerCase()),m.delimiter){case"all":h=/(.)/;break;case"letter":case"char":case"character":h=/(\S)/;break;case"word":h=/\s*(\S+)\s*/;break;case"sentence":h=/(?=\S)(([.]{2,})?[^!?]+?([.â€¦!?]+|(?=\s+$)|$)(\s*[â€²â€™'â€â€³â€œ")Â»]+)*)/;break;case"element":h=/(?=\S)([\S\s]*\S)/;break;default:if(!(m.delimiter instanceof RegExp))return n.log(i+": Unrecognized delimiter, empty search string, or invalid custom Regex. Aborting."),!0;h=m.delimiter}if(this.each(function(){var e=$(this),r=e.text();if(d!==!1){f={blastedIndex:0,nodeBeginning:!1,wrappers:f.wrappers||[]},e.data(i)===a||"search"===e.data(i)&&m.search!==!1||(p(e,m),m.debug&&n.log(i+": Removed element's existing Blast call.")),e.data(i,m.search!==!1?"search":m.delimiter),m.aria&&e.attr("aria-label",r),m.stripHTMLTags&&e.html(r);try{t.createElement(m.tag)}catch(s){m.tag="span",m.debug&&n.log(i+": Invalid tag supplied. Defaulting to span.")}e.addClass(i+"-root"),m.debug&&n.time(i),g(this,m),m.debug&&n.timeEnd(i)}else d===!1&&e.data(i)!==a&&p(e,m);m.debug&&$.each(f.wrappers,function(e,t){n.log(i+" ["+m.delimiter+"] "+this.outerHTML),this.style.backgroundColor=e%2?"#f12185":"#075d9a"})}),d!==!1&&m.returnGenerated===!0){var b=$().add(f.wrappers);return b.prevObject=this,b.context=this.context,b}return this},$.fn.blast.defaults={returnGenerated:!0,delimiter:"word",tag:"span",search:!1,customClass:"",generateIndexID:!1,generateValueClass:!1,stripHTMLTags:!1,aria:!0,debug:!1}}(window.jQuery||window.Zepto,window,document);

$("#nav_bar nav a").removeClass("active");
$(".home-link").addClass("active");

$(".home-page h1").blast({
    delimiter: "character",
    tag: "span"
});

a = 0;
$(".home-page .blast").each(function() {
    if (a == 300) {
        a = 400;
    }

    if (a == 1200) {
        a = 1400;
    }

    var el = $(this);

    if (a == 400) {
        setTimeout(function() {
            $(".home-page h1 img").addClass("animated rotateIn");
        }, 500);
    }

    setTimeout(function() {
        el.addClass("animated bounceIn");
    }, a);

    if (a < 1200) {
        a = a + 100;
    } else {
        a = a + 70;
    }
});
setTimeout(function() {
    $(".home-page .blast").removeClass("animated bounceIn");
    $(".home-page .blast").css("opacity", 1);

    $(".home-page .blast").mouseenter(function() {
        var el = $(this);

        $(this).addClass("animated rubberBand");
        $(
            this
        ).one(
            "webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend",
            function() {
                el.removeClass("animated rubberBand");
            }
        );
    });
}, 3000);

setTimeout(function() {
    $(".home-page .flat-button").addClass("animated bounceIn");
    $(
        ".home-page .flat-button"
    ).one(
        "webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend",
        function() {
            $(".home-page .flat-button").removeClass("animated bounceIn");
            $(".home-page .flat-button").css("opacity", 1);
        }
    );
}, 2000);

$(".home-page .flat-button").mouseenter(function() {
    var el = $(this);

    $(this).addClass("animated rubberBand");
    $(
        this
    ).one(
        "webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend",
        function() {
            el.removeClass("animated rubberBand");
        }
    );
});