var lstsis =
    "Envio de Mensagens do Portal - EMP|Materiais|Consulta Cadastro MEGA |Catalogo de Itens em Geral|Materiais Geral|Uso Comum|Bolsa de Estudos |Solicitação/Renovação|Cesta Básica|Confraternização|Dicionário Aurélio|Documentos Normativos|Pesquisa de Clima Organizacional";

var lstsisurl = "#|#|#|#|#|#|#|#|#|#|#|#|#";

lstsis = lstsis.split("|");
lstsisurl = lstsisurl.split("|");

//Preloader
$(document).ready(function () {
    $(".loader, .lds-ring").fadeOut();
});
//Menu Office 365 close without hover --------------
$("#collapseExample").hover(
    function () {
        //não faça nada
    },
    function () {
        $(this).collapse("hide"); //fecha o content do menu
    }
);

(function (window) {
    "use strict";

    function extend(a, b) {
        for (var key in b) {
            if (b.hasOwnProperty(key)) {
                a[key] = b[key];
            }
        }
        return a;
    }

    function CBPFWTabs(el, options) {
        this.el = el;
        this.options = extend({}, this.options);
        extend(this.options, options);
        this._init();
    }

    CBPFWTabs.prototype.options = {
        start: 0
    };

    CBPFWTabs.prototype._init = function () {
        // tabs elems
        this.tabs = [].slice.call(this.el.querySelectorAll("nav > ul > li"));
        // content items
        this.items = [].slice.call(
            this.el.querySelectorAll(".content-wrap > section")
        );
        // current index
        this.current = -1;
        // show current content item
        this._show();
        // init events
        this._initEvents();
    };

    CBPFWTabs.prototype._initEvents = function () {
        var self = this;
        this.tabs.forEach(function (tab, idx) {
            tab.addEventListener("click", function (ev) {
                ev.preventDefault();
                self._show(idx);
            });
        });
    };

    CBPFWTabs.prototype._show = function (idx) {
        if (this.current >= 0) {
            this.tabs[this.current].className = this.items[this.current].className =
                "";
        }
        // change current
        this.current =
            idx != undefined
                ? idx
                : this.options.start >= 0 && this.options.start < this.items.length
                    ? this.options.start
                    : 0;
        this.tabs[this.current].className = "tab-current";
        this.items[this.current].className = "content-current";
    };

    // add to global namespace
    window.CBPFWTabs = CBPFWTabs;
})(window);

(function () {
    [].slice.call(document.querySelectorAll(".tabs")).forEach(function (el) {
        new CBPFWTabs(el);
    });
})();

function renderList(filter = "") {
    let inputHtml = "";
    let filteredList = [];
    let linkList = [];

    if (filter.length > 0) {
        filteredList = lstsis.filter((item, index) => {
            if (item.toLowerCase().includes(filter.toLowerCase())) {
                linkList.push(lstsisurl[index]);
                return true;
            } else {
                return false;
            }
        });
    }

    filteredList.forEach(
        (item, index) =>
            (inputHtml += `
		  	<div class="card col-md-12 my-2" style="border: 1px solid rgb(222, 239, 255);border-radius: 8px;">
             <div class="card-body">
              <div class="row">

				<div class="col-md-10 my-1">
	              <p class="card-title">${item}</p>
	            </div>
	            <div class="col-md-2 my-1">
	              <a href="${linkList[index]}" class="btn-sm button-link blue-color w-100">Acessar</a>
	            </div>

              </div>
            </div>
          </div>
			`)
    );
    $("#result").html(inputHtml);
}

$("#pesquisageral").on("input", function (e) {
    let pesquisageral = $("#pesquisageral").val();
    renderList(pesquisageral);
});

renderList();

for (var i = 0; i < lstsis.length; i++) {
    var newClass = lstsis[i].substring(0, 33);

    var printCards =
        "<a href='" +
        lstsisurl[i] +
        "' class='" +
        newClass +
        " academico card cards-func shadow-card rounded-lg border-0 d-flex justify-content-center'><span class='text-primary font-weight-normal px-3 text-left'>" +
        lstsis[i] +
        "</span><span class='arrow-card'></span></a>";

    document.querySelector(".sysacad").innerHTML += printCards;
}

var nameSystems = [
    "Solicitações",
    "Normas",
    "Ramais e E-mails",
    "Estatuto e Regimento Geral",
    "TI",
    "Aviso de férias",
    "Conselho Universitário",
    "Módulos",
    "Materiais",
    "Uso Comum",
    "Informações"
];

for (var i = 0; i < nameSystems.length; i++) {
    var elementoHTML = document.getElementsByClassName(nameSystems[i]);
    $("<div class='titulo'><h3>" + nameSystems[i] + "</h3></div>").insertBefore(
        elementoHTML[0]
    );
}

$(document).ready(function () {
    $.get(
        "http://apidev.accuweather.com/currentconditions/v1/45883.json?language=pt&apikey=hoArfRosT1215",
        function (data) {
            $("#temperatura").html(data[0].Temperature.Metric.Value + " °c");
            $("#clima").html("Céu: " + data[0].WeatherText);
            $("#icone").attr(
                "src",
                "https://vortex.accuweather.com/adc2010/images/slate/icons/" +
                data[0].WeatherIcon +
                ".svg"
            );
        }
    );
});

$("a.cards-func").click(function (event) {
    if ($(this).attr("target") != "_blank") {
        //Se for diferente de target _blank ele abrirá em iframe

        event.preventDefault(); //cancela a ação padrão do click (cancela o redirecionamento a href)
        var url = $(this).attr("href"); //pega o atributo href do card clicado e passa para a variavel URL
        $(".loader, .lds-ring").fadeIn(); //inicia o loader
        $("#iframe").attr("src", url); //insere a url correta para rodar no iframe

        $("#page-content-wrapper, .page-loader").toggleClass("d-none"); //para o iframe ser exibido o content principal deve ser ocultado

        /* Manipula o iframe para aplicar correções no estilo da intranet antiga
         ** Oculta os menus de topo, entre outros itens da antiga intranet */
        $("#iframe").on("load", function () {
            $("#iframe")
                .contents()
                .find("head")
                .append(
                    "<style>#pc_user { display: none;} #pc_sair { display: none;} #pc_fundomenu { display: none;}#pc_busca { display: none;} #PC_brilho { display: none !important; } #pc_centro { position: inherit !important; }</style>"
                );

            $(".loader, .lds-ring").fadeOut(); //encerra o loader
        });
    }
});

$(".close-card").click(function () {
    $(this).prev().toggleClass("cards-short--disable");
    $(this).prev().toggleClass("cards-short");
    $(this).toggleClass("transform-45");
});

$(".form-short .submit").click(function () {
    var appendItems = $(".modal-body.shortcuts").find(".cards-short").parent(); //verifica todos os itens com a classe e pega o elemento completo
    $(appendItems).removeClass("cards-short--disable");
    $(".block.shortcuts").append(appendItems); //insere o(os) item(ns) nos atalhos
});

$(".remove").click(function () {
    var returnItems = $(".block.shortcuts")
        .find(".cards-short--disable")
        .parent(); //verifica todos os itens com a classe e pega o elemento completo
    $(".modal-body.shortcuts").append(returnItems); //insere o(os) item(ns) no modal
});

function toggleDarkLight() {
    var body = document.getElementById("page-content-wrapper");
    var frame = document.getElementById("page-content-frame");
    if ($(body).hasClass("dark-mode")) {
        //a condição verifica se no elemento content principal existe a classe "dark-mode"
        body.className = "";
        frame.className = "page-loader d-none";
    } else {
        body.className = "dark-mode";
        frame.className = "page-loader dark-mode d-none";
    }

    /* Troca a imagem de bem-vindo para o modo dark
     ** Na imagem existem dois atributos de imagem
     ** Quando o modo dark é alternado, esse links também precisar ser alternados */
    var _this = $("#welcome");
    var current = _this.attr("src");
    var swap = _this.attr("data-swap");
    _this.attr("src", swap).attr("data-swap", current);
}

$("#menu-toggle").click(function (e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
});

$("#myModal").on("shown.bs.modal", function () {
    $("#myInput").trigger("focus");
});

function geral() {
    $(".display").css("display", "none");
    $(".atalhos").css("display", "block");
    $("#page-content-wrapper").removeClass("d-none");
    $(".page-loader").addClass("d-none");
}

//Click Sidenav menu
$(".list-group a").click(function () {
    $(".list-group a.text-secondary").removeClass("active");
    $("html, body").animate({ scrollTop: 0 }, 500); //Scroll top para suavizar a troca de tela
    $(this).addClass("active");

    //Alterna a exibição de telas

    //Array de classes verificadas na Sidenav
    const arrayMenu = ["home", "acad", "adm", "beneficios", "visoes", "info"];

    for (var i = 0; i < arrayMenu.length; i++) {
        if ($(this).hasClass(arrayMenu[i])) {
            geral();
            $("." + arrayMenu[i] + ".display").css("display", "block");
        }
    }
});
