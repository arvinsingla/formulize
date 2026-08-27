<?php

// Anari's drawer wrapper. Anari scopes its form field styling to
// `.form-container` (see themes/Anari/css/style.css), which its form and
// multiPage screens emit inside a card. The drawer is the card here, so only
// the container is repeated - the fields then match the full-screen form.

print "
<div class='form-container formulize-drawer-form'>
";
