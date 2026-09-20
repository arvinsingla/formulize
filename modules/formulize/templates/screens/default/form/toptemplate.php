<?php

// A single page form screen has no page-nav strip, so the print and "Office Use
// Only" icon buttons ($headerActions, issue #151) go at the right hand end of the
// card header, across from the form title. Nothing is sticky on this kind of
// screen, so unlike the multipage strip they scroll with the form.

print "
<div class='card'>

<div class='card__header'>
	<h3 class='card__title'>".$formTitle."</h3>
	".$headerActions."
</div>

<div class='card__body'>
<div class='form-container'>
";