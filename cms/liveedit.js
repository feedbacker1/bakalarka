
//document.body.addEventListener('DOMSubtreeModified', function (a, b) {
//    console.log(a, b);
//});

// document.body.addEventListener('DOMNodeInserted', function(e) {
//     $(e.target).attr('data-misocms');
// });
//
//
// var observer = new MutationObserver(function(mutations) {
//     mutations.forEach(function(mutation, el) {
//         console.log(mutation, el);
//     });
// });
//
// var observerConfig = {
//     childList: true
// };
//
// var targetNode = document.body;
// observer.observe(targetNode, observerConfig);


if (typeof jQuery == 'undefined') {
    var script = document.createElement('script');
    script.type = "text/javascript";
    script.dataset.misocmsNoParse = "";
    script.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js";
    document.getElementsByTagName('head')[0].appendChild(script);
}

if (typeof jQuery.ui == 'undefined') {

    var style = document.createElement('link');
    style.rel = "stylesheet";
    style.dataset.misocmsNoParse = "";
    style.href = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css";
    document.getElementsByTagName('head')[0].appendChild(style);

    var script = document.createElement('script');
    script.type = "text/javascript";
    script.dataset.misocmsNoParse = "";
    script.src = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js";
    document.getElementsByTagName('head')[0].appendChild(script);

}

var style = document.createElement('link');
style.rel = "stylesheet";
style.dataset.misocmsNoParse = "";
style.href = "http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css";
document.getElementsByTagName('head')[0].appendChild(style);

var script = document.createElement('script');
script.type = "text/javascript";
script.dataset.misocmsNoParse = "";
script.src = "cms/ckeditor/ckeditor.js";
document.getElementsByTagName('head')[0].appendChild(script);


var script = document.createElement('script');
script.type = "text/javascript";
script.dataset.misocmsNoParse = "";
script.src = "cms/html2canvas/html2canvas.min.js";
document.getElementsByTagName('head')[0].appendChild(script);

var script = document.createElement('script');
script.type = "text/javascript";
script.dataset.misocmsNoParse = "";
script.src = "cms/html2canvas/html2canvas.svg.min.js";
document.getElementsByTagName('head')[0].appendChild(script);



$("body").append("<style>\n" +
    "#misocms-panel {z-index: 13501;position:fixed; top:50px; left:50px; text-align:center; color: black; background: rgba(220,220,220,0.7); border:2px solid rgb(160,160,160); width: 150px; height: 180px; padding: 0.5em; -webkit-transition: background 0.3s; transition: background 0.3s; }\n" +
    "#misocms-panel:hover, #misocms-content:hover, #misocms-blocks:hover, #misocms-content-add-block:hover {background: rgba(220,220,220,1); }\n" +
    "div.button-fa {display: inline-block; border-radius: 4px; background-color: whitesmoke; border: 1px solid lightgrey; width: 40px; height: 40px; font-size: 26px; margin: 2px; padding-top: 6px;}\n" +
    "div.picker-enabled, div.btn-active {background-color: grey;}\n" +
    "div.button-fa:hover {cursor: pointer}\n" +
    ".hovered {background-color: rgba(240,40,40,0.3); cursor: crosshair} \n" +
    ".activated {background-color: rgba(240,40,40,0.6); cursor: default} \n" +
    "#misocms-editor-menu .btn {margin: 5px auto;}\n" +
    ".fa-arrows:hover {cursor: move;}\n" +
    ".fa-close:hover {cursor: pointer;}\n" +
    ".block-preview img {width: 166px;max-height: 150px;}\n" +
    ".block-preview:hover {background-color: rgba(0,0,0,0.3); cursor: pointer}\n" +
    "#misocms-content {z-index: 13502;display:none; position:fixed; top:50px; right:50px; color: black; background: rgba(220,220,220,0.7); border:2px solid rgb(160,160,160); width: 600px; height: 360px; padding: 0.5em; -webkit-transition: background 0.3s; transition: background 0.3s;}\n" +
    "#misocms-content-add-block {z-index: 13503;display:none; position:fixed; bottom:50px; right:50px; color: black; background: rgba(220,220,220,0.7); border:2px solid rgb(160,160,160); width: 220px; height: 120px; padding: 0.5em; -webkit-transition: background 0.3s; transition: background 0.3s;}\n" +
    "#misocms-blocks {z-index: 13504;display:none; position:fixed; left:50px; bottom:50px; color: black; background: rgba(220,220,220,0.7); border:2px solid rgb(160,160,160); width: 200px; height: 360px; padding: 0.5em; -webkit-transition: background 0.3s; transition: background 0.3s;}\n" +
    "#misocms-blocks .blocks-list {display: block; height: 300px; overflow-y: scroll; overflow-x: hidden;}\n" +
    "#misocms-preloader {z-index: 13500;position: fixed;left: 0; top: 0; width:100%; height: 100%; background: rgba(0,0,0,0.7);}" +
"  </style>");



$("body").append("<div data-misocms-no-parse id=\"misocms-panel\" class=\"draggable ui-widget-content\">\n" +
"<span style='float:left'><i class='fa fa-arrows'></i></span> <h4>Menu</h4>\n" +
"<div onclick='picker_mode(this)' class='button-fa'><i class='button-fa fa fa-crosshairs'></i></div>\n" +
"<div onclick='add_element(this)' class='button-fa'><i class='button-fa fa fa-plus'></i></div>\n" +
    "<br /><br />" +
"<button onclick='save_file()' style='width: 100%;' class='btn btn-success btn-sm'><i class='fa fa-save'></i> Uložiť</button><br />\n" +
"<a href='admin'><button style='width: 100%;margin-top: 6px' class='btn btn-default btn-xs'><i class='fa fa-dashboard'></i> Administrácia</button></a><br />\n" +
"</div>");



$("body").append("<div data-misocms-no-parse id=\"misocms-content\" class=\"draggable ui-widget-content\">\n" +
"<span style='float:left;'><i class='fa fa-arrows'></i></span> <h4 style='display: inline-block; width: 552px; text-align: center; margin-top: 0; margin-bottom: 10px;'>Editácia obsahu</h4> <span style='float: right;'><i class='fa fa-close'></i></span> \n" +
"<div style='display: inline-block;width:80%;padding-right: 10px;'>\n" +
"<textarea style='width: 100%; height: 280px;' id=\"misocms-editor\" name='misocms-editor'></textarea>\n" +
"</div>\n" +
"<div style='display: inline-block;width:20%;float: right;text-align: center' id='misocms-editor-menu'>\n" +
    "<button onclick='shift_up()' style='width: 48%;' class='btn btn-default btn-sm'><i class='fa fa-arrow-up'></i> </button>\n" +
    "<button onclick='shift_down()' style='width: 48%;' class='btn btn-default btn-sm'><i class='fa fa-arrow-down'></i> </button>\n" +
    "<button onclick='create_block(this)' style='width: 100%;' class='btn btn-primary btn-sm'><i class='fa fa-plus'></i> Vytvoriť blok</button><br />\n" +
    "<button onclick='delete_block(this)' style='width: 100%;' class='btn btn-danger btn-sm'><i class='fa fa-trash'></i> Odstrániť blok</button><br />\n" +
    "<br />\n" +
"</div>\n" +
"</div>");



$("body").append("<div data-misocms-no-parse id=\"misocms-blocks\" class=\"draggable ui-widget-content\">\n" +
    "<span style='float:left;'><i class='fa fa-arrows'></i></span> <h4 style='display: inline-block; width: 142px; text-align: center; margin-top: 0; margin-bottom: 10px;'>Bloky</h4> <span style='float: right;'><i class='fa fa-close'></i></span> " +
    "<div class='blocks-list'></div>\n" +
    "</div>");

$("body").append("<div data-misocms-no-parse id=\"misocms-content-add-block\" class=\"draggable ui-widget-content\">\n" +
    "<span style='float:left;'><i class='fa fa-arrows'></i></span> <h4 style='display: inline-block; width: 172px; text-align: center; margin-top: 0; margin-bottom: 10px;'>Pridanie bloku</h4> <span style='float: right;'><i class='fa fa-close'></i></span> \n" +
    "<button onclick='shift_up()' style='width: 48%;' class='btn btn-default btn-sm'><i class='fa fa-arrow-up'></i> </button>\n" +
    "<button onclick='shift_down()' style='width: 48%;' class='btn btn-default btn-sm'><i class='fa fa-arrow-down'></i> </button>\n" +
    "<button onclick='get_html_of_element()' style='width: 97%;margin-top:6px;' class='btn btn-sm btn-primary'>Vložiť sem</button>" +
    "</div>");

$("body").append("<div id=\"misocms-preloader\"> </div>");

var editor;

$( window ).on( "load", function() {
    $( ".draggable" ).draggable({
        handle: "i.fa-arrows"
    });

    $("#misocms-preloader").hide();

    editor = CKEDITOR.replace( 'misocms-editor', {
        filebrowserBrowseUrl: '/ckfinder/ckfinder.html',
        filebrowserImageBrowseUrl: '/ckfinder/ckfinder.html?type=Images',
        filebrowserUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
        filebrowserImageUploadUrl: '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
        enterMode	: CKEDITOR.ENTER_BR
    } );
});

var picker_enabled = false;
var appender_enabled = false;

var selected_block = null;
var prev_element = [];

function hover_in(ev) {

    if (picker_enabled) {
        var el = $(ev.target);
        el.addClass('hovered');
        el.click(function (e) {
            if (picker_enabled && !$(e.target).hasClass("button-fa")) {
                var el = $(e.target);

                e.preventDefault();

                el.addClass('activated');
                picker_enabled = false;
                handle_activated_element(el);
            }
        });
    } else if (appender_enabled) {
        var el = $(ev.target);
        el.addClass('hovered');
        el.click(function (e) {
            if (appender_enabled && !$(e.target).hasClass("button-fa")) {
                var el = $(e.target);

                e.preventDefault();

                el.addClass('activated');
                appender_enabled = false;
                handle_appended_element(el);
            }
        });
    }
}

function hover_out(ev) {
    if (picker_enabled || appender_enabled) {
        var el = $(ev.target);
        el.removeClass('hovered');
    }
}

function shift_up() {
    prev_element.push($(".activated").data("misocms-id"));
    var data = $(".activated").removeClass("activated").removeClass("hovered").parent().addClass("activated").addClass("hovered").html();
    editor.setData(data);
}

function shift_down() {

    var prev_id = prev_element.pop();

    $(".activated").removeClass("activated").removeClass("hovered");
    $("[data-misocms-id='" + prev_id + "']").addClass("activated").addClass("hovered");
    editor.setData($("[data-misocms-id='" + prev_id + "']").html());
}

$("#misocms-content .fa-close").click(function () {
    $(".activated").removeClass("activated").removeClass("hovered");
    $(".fa-crosshairs").parent().removeClass("picker-enabled");
    picker_enabled = false;
    $("#misocms-content").hide();
});

$("#misocms-blocks .fa-close").click(function () {
    $("#misocms-content-add-block").hide();
    $("#misocms-blocks").hide();
    $("#misocms-panel .fa-plus").parent().removeClass("btn-active");
    $(".activated").removeClass("activated").removeClass("hovered");
    $("#misocms-preloader").hide();
    appender_enabled = false;
});

$("#misocms-content-add-block .fa-close").click(function () {
    $("#misocms-content-add-block").hide();
    $("#misocms-blocks").hide();
    $("#misocms-panel .fa-plus").parent().removeClass("btn-active");
    $(".activated").removeClass("activated").removeClass("hovered");
    appender_enabled = false;
});

function picker_mode(button) {

    if (!$(button).hasClass("picker-enabled")) {
        $(button).addClass("picker-enabled");
        picker_enabled = true;
    } else {
        $(".activated").removeClass("activated").removeClass("hovered");
        $(button).removeClass("picker-enabled");
        picker_enabled = false;
        $("#misocms-content").hide();
    }

    if (picker_enabled) {
        $(".activated").removeClass("activated").removeClass("hovered");
        $("body * :not(.draggable *)").mouseover(hover_in);
        $("body * :not(.draggable *)").mouseout(hover_out);
        picker_enabled = true;
    }
}

function appender_mode(el) {

    selected_block =  $(el).data("misocms-block-id");

    $("body * :not(.draggable *)").mouseover(hover_in);
    $("body * :not(.draggable *)").mouseout(hover_out);
    appender_enabled = true;

    $("#misocms-preloader").hide();

}

function load_blocks() {
    $.ajax({
        method: "POST",
        url: "cms/ajax.php",
        data: { type: "get_blocks" }
    })
        .done(function( msg ) {
            var data = JSON.parse(msg.toString());
            $("#misocms-blocks .blocks-list").html("");
            $(data).each(function (i) {
                $("#misocms-blocks .blocks-list").append("<div onclick='appender_mode(this)' data-misocms-block-id='" + data[i].element_id + "' class='block-preview'><p>" + data[i].name + "</p><img src='" + data[i].image + "'></div>");
            })
        });

    $("#misocms-blocks").show();
    $("#misocms-preloader").show();

}

function add_element(button) {

    if (!$(button).hasClass("btn-active")) {
        $(button).addClass("btn-active");

        load_blocks();
    } else {
        $(button).removeClass("btn-active");
        $("#misocms-blocks").hide();
        $("#misocms-preloader").hide();
    }

}

function handle_activated_element(el) {
    $("#misocms-content").show();
    editor.setData($(el).html());

    editor.on( 'change', function( evt ) {
        document.body.addEventListener('DOMNodeInserted', function(e) { if(e.target.nodeType == 1) { e.target.removeAttribute('data-misocms-no-parse') }});
        $(".activated").html(evt.editor.getData());
        document.body.addEventListener('DOMNodeInserted', function(e) { if(e.target.nodeType == 1) { e.target.setAttribute('data-misocms-no-parse', '') }});
    });
}

function handle_appended_element(el) {
    $("#misocms-content-add-block").show();
}

function save_file() {
    $.ajax({
        method: "POST",
        url: "cms/ajax.php",
        data: { type: "parse", html: $("html").html(), filename: window.location.search.substring(1).split("=")[1] }
    })
        .done(function( msg ) {
            alert( "Stránka bola uložená do databázy." );
            if (confirm( "Chcete vygenerovať aj .html súbor na ostrú verziu stránky?\n \nPOZOR!\ntento krok sa nedá vrátiť späť" )) {
                $.ajax({
                    method: "POST",
                    url: "cms/ajax.php",
                    data: { type: "save", filename: window.location.search.substring(1).split("=")[1] }
                })
                    .done(function( msg ) {
                        if (msg == "success") {
                            alert("HTML súbor bol vytvorený");
                        }
                    });
            }
        });
}

function create_block(el) {

    var elem_id = $(".activated").data("misocms-id");
    var elem = document.querySelectorAll("[data-misocms-id='" + elem_id + "']")[0];
    var img = "";
    var name = prompt("Pomenujte blok", "napr. Položka menu");
    $(".activated").removeClass("activated").removeClass("hovered");

    html2canvas(elem, {
        onrendered: function(canvas) {
            img = canvas.toDataURL("image/png");
            $(elem).addClass("activated", "hovered");

            $.ajax({
                method: "POST",
                url: "cms/ajax.php",
                data: {
                    type: "create_block",
                    id: elem_id,
                    filename: window.location.search.substring(1).split("=")[1],
                    image: img,
                    name: name
                }
            })
                .done(function( msg ) {
                    load_blocks();
                });

        }
    });
}

function delete_block(el) {

    var elem_id = $(".activated").data("misocms-id");
    var elem = document.querySelectorAll("[data-misocms-id='" + elem_id + "']")[0];

    $(elem).remove();
    $("#misocms-content").hide();
    picker_enabled = false;
    $(".button-fa.picker-enabled").removeClass("picker-enabled");
}

function get_html_of_element() {

    $.ajax({
        method: "POST",
        url: "cms/ajax.php",
        data: {
            type: "get_html_of_element",
            id: selected_block
        }
    })
        .done(function( msg ) {
            document.body.addEventListener('DOMNodeInserted', function(e) { if(e.target.nodeType == 1) { e.target.removeAttribute('data-misocms-no-parse') }});
            $(".activated").after(msg);
            document.body.addEventListener('DOMNodeInserted', function(e) { if(e.target.nodeType == 1) { e.target.setAttribute('data-misocms-no-parse', '') }});
            $(".activated").removeClass("hovered").removeClass("activated");
            $("#misocms-content-add-block").hide();
            $("#misocms-blocks").hide();
            appender_enabled = false;
            $(".btn-active").removeClass("btn-active");
        });
}
