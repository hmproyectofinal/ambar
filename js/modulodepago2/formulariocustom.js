function getHtml(){
	return document.getElementById("html_formulariocustom").value;
}

function getCss(){
	css =  document.getElementById("css_formulariocustom").value;
	return "<style>"+css+"</style>"	
}

function cambiarContenido(){
	css= this.getCss();
	html = this.getHtml();
	document.getElementById("vista_formualriocustom").innerHTML = css+html;
}

function preguntaSeguridad(){
	if (confirm('¿Esta seguro que desea modicar el formulario? \n (esta acción no se puede deshacer)')){ 
       document.formulariocustom.submit() 
    } 

}