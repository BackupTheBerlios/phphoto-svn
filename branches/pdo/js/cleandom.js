/*
	cleanDOM prototype by Pawel Knapik // pawel.saikko.com
	
	removes empty nodes and comments from element DOM to allow safe and cross-browser DOM navigation (accessing nextSibling, firstChild etc)
*/
Object.prototype.cleanDOM = function(){
var z=this.childNodes,i;z?(i=z.length):'';
while(i--)(z[i].nodeType==3&&!/\S/.test(z[i].nodeValue)||z[i].nodeType==8)?this.removeChild(z[i]):(z[i].hasChildNodes)?z[i].cleanDOM():'';
}
