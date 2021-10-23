"use strict";

function expandTextArea(textarea)
{
	textarea.style.height = 'auto';
	textarea.style.height = (textarea.scrollHeight) + 'px';
}

let textareas = document.getElementsByTagName('textarea');
for (let textarea of textareas) {
	textarea.setAttribute('style', 'height:' + (textarea.scrollHeight) + 'px;overflow-y:hidden;');
	textarea.addEventListener("input", ev => expandTextArea(ev.target), false);
}
