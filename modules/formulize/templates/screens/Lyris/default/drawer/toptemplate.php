<?php

// Lyris's drawer wrapper. `.fz-form-screen` carries the form-screen density
// tokens and the field styling; the inner container carries the design-system
// label-mode + density modifiers. These are exactly the classes Lyris's form and
// multiPage screens emit (minus the card, which the drawer itself plays the part
// of), so a form in the drawer picks up the identical rules it does full screen.

print "
<div class='fz-form-screen formulize-drawer-form'>
<div class='fz-form fz-form--label-top fz-form--compact form-container'>
";
