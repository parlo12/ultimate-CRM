!function(t,e){"use strict";var o=e.querySelector(".basic-toast"),r=e.querySelector(".toast-basic-toggler"),a=new bootstrap.Toast(o);r.addEventListener("click",(function(){a.show()}));[].slice.call(e.querySelectorAll(".toast")).map((function(t){return new bootstrap.Toast(t)}));var s=e.querySelector(".toast-autohide"),c=e.querySelector(".toast-autohide-toggler"),n=new bootstrap.Toast(s,{autohide:!1});c.addEventListener("click",(function(){n.show()}));var i=e.querySelector(".toast-stacked"),u=e.querySelector(".toast-stacked-toggler"),l=new bootstrap.Toast(i);u.addEventListener("click",(function(){l.show()}))}(window,document,jQuery);
