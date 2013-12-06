<?
defined('C5_EXECUTE') or die("Access Denied.");

$al = AssetList::getInstance();

/** 
 * Third party libraries we rely on
 */

// jquery
$al->register('javascript', 'jquery', 'js/jquery.js', array('weight' => 100, 'position' => Asset::ASSET_POSITION_HEADER, 'postprocess' => false));

// jquery ui
$al->register('javascript', 'jqueryui', 'js/jquery.ui.js', array('weight' => 95, 'postprocess' => false));
$al->register('css', 'jqueryui', 'css/jquery.ui.css', array('postprocess' => false));

$al->registerGroup('jqueryui', array(
	array('javascript', 'jqueryui'),
	array('css', 'jqueryui')
));

// Underscore
$al->register('javascript', 'underscore', 'js/underscore.js');

// dropzone
$al->register('javascript', 'dropzone', 'js/dropzone.js');

// jquery form 
$al->register('javascript', 'jquery/form', 'js/jquery.form.js');

// jquery rating
$al->register('javascript', 'jquery/rating', 'js/jquery.rating.js');
$al->register('css', 'jquery/rating', 'css/jquery.rating.css', array('postprocess' => false));
$al->registerGroup('jquery/rating', array(
	array('javascript', 'jquery/metadata'),
	array('javascript', 'jquery/rating'),
	array('css', 'jquery/rating')
));

// jquery color picker
$al->register('javascript', 'jquery/colorpicker', 'js/jquery.colorpicker.js');
$al->register('css', 'jquery/colorpicker', 'css/jquery.colorpicker.css', array('postprocess' => false));
$al->registerGroup('jquery/colorpicker', array(
	array('javascript', 'jquery/colorpicker'),
	array('css', 'jquery/colorpicker')
));

// swfobject
$al->register('javascript', 'swfobject', 'js/swfobject.js');

// redactor
$al->register('javascript', 'redactor', 'js/redactor.js', array('postprocess' => false));
$al->register('css', 'redactor', 'css/redactor.css');
$al->registerGroup('redactor', array(
	array('javascript', 'redactor'),
	array('css', 'redactor')
));

// backstretch
$al->register('javascript', 'backstretch', 'js/jquery.backstretch.js');

// dynatree
$al->register('javascript', 'dynatree', 'js/dynatree.js', array('postprocess' => false));
$al->register('css', 'dynatree', 'css/dynatree.css', array('postprocess' => false));
$al->registerGroup('dynatree', array(
	array('javascript', 'dynatree'),
	array('css', 'dynatree')
));

// hoverIntent
$al->register('javascript', 'hoverintent', 'js/ccm_app/jquery.hoverIntent.js');

// bootstrap
$al->register('javascript', 'bootstrap/dropdown', 'js/bootstrap/dropdown.js');
$al->register('javascript', 'bootstrap/tooltip', 'js/bootstrap/tooltip.js', array('weight' => 95)); // has to come before popover
$al->register('javascript', 'bootstrap/popover', 'js/bootstrap/popover.js');
$al->register('javascript', 'bootstrap/alert', 'js/bootstrap/alert.js');
$al->register('javascript', 'bootstrap/button', 'js/bootstrap/button.js');
$al->register('javascript', 'bootstrap/transition', 'js/bootstrap/transition.js');
$al->register('css', 'bootstrap/dropdown', 'css/ccm.app.css', array('postprocess' => false));
$al->register('css', 'bootstrap/tooltip', 'css/ccm.app.css', array('postprocess' => false));
$al->register('css', 'bootstrap/popover', 'css/ccm.app.css', array('postprocess' => false));
$al->register('css', 'bootstrap/alert', 'css/ccm.app.css', array('postprocess' => false));
$al->register('css', 'bootstrap/transition', 'css/ccm.app.css', array('postprocess' => false));
$al->register('css', 'bootstrap/button', 'css/ccm.app.css', array('postprocess' => false));
$al->register('css', 'bootstrap', 'css/ccm.app.css', array('postprocess' => false));

/** 
 * ## Core functionality and styles
 */

// JS Events
$al->register('javascript', 'core/observer', 'js/ccm.pubsub.js');

// Core App
$al->register('css', 'core/app', 'css/ccm.app.css', array('postprocess' => false));
$al->register('javascript', 'core/app', 'js/ccm.app.js', array('postprocess' => false));
$al->registerGroup('core/app', array(
	array('javascript', 'jquery'),
	array('javascript', 'core/observer'),
	array('javascript', 'underscore'),
	array('javascript', 'bootstrap/dropdown'),
	array('javascript', 'bootstrap/popover'),
	array('javascript', 'bootstrap/tooltip'),
	array('javascript', 'jqueryui'),
	array('javascript', 'core/app'),
	array('css', 'core/app'),
	array('css', 'jqueryui')
));

// Image Editor
$al->register('javascript', 'kinetic', 'js/kinetic.js');
$al->register('css', 'core/imageeditor', 'css/ccm.image_editor.css');
$al->register('javascript', 'core/imageeditor', 'js/ccm.imageeditor.js');
$al->registerGroup('core/imageeditor', array(
	array('javascript', 'kinetic'),
	array('javascript', 'core/imageeditor'),
	array('css', 'core/imageeditor')
));


// Dashboard
$al->register('css', 'dashboard', 'css/ccm.dashboard.css');
$al->register('javascript', 'dashboard', 'js/ccm.dashboard.js');
$al->registerGroup('dashboard', array(
	array('javascript', 'jquery'),
	array('javascript', 'jqueryui'),
	array('javascript', 'underscore'),
	array('javascript', 'dashboard'),
	array('javascript', 'backstretch'),
	array('javascript', 'core/observer'),
	array('javascript', 'bootstrap/dropdown'),
	array('javascript', 'bootstrap/popover'),
	array('javascript', 'bootstrap/tooltip'),
	array('javascript', 'bootstrap/transition'),
	array('javascript', 'bootstrap/alert'),
	array('javascript', 'core/app'),
	array('javascript', 'redactor'),
	array('css', 'core/app'),
	array('css', 'redactor'),
	array('css', 'jqueryui'),
	array('css', 'dashboard')
));


// Basic styles (used to be in ccm.base.css)
$al->register('css', 'core/frontend/captcha', 'css/frontend/captcha.css');
$al->register('css', 'core/frontend/pagination', 'css/frontend/pagination.css');
$al->register('css', 'core/frontend/errors', 'css/frontend/errors.css');

// Sitemap
$al->register('javascript', 'core/sitemap', 'js/ccm.sitemap.js', array('postprocess' => false));
$al->register('css', 'core/sitemap', 'css/ccm.sitemap.css', array('postprocess' => false));
$al->registerGroup('core/sitemap', array(
	array('javascript', 'core/observer'),
	array('javascript', 'core/sitemap'),
	array('javascript', 'dynatree'),
	array('css', 'dynatree'),
	array('css', 'core/sitemap')
));

// Topics
$al->register('javascript', 'core/topics', 'js/ccm.topics.js', array('postprocess' => false));
$al->register('css', 'core/topics', 'css/ccm.topics.css', array('postprocess' => false));
$al->registerGroup('core/topics', array(
	array('javascript', 'core/topics'),
	array('javascript', 'dynatree'),
	array('css', 'dynatree'),
	array('css', 'core/topics')
));

// Groups (Group Tree)
$al->register('javascript', 'core/groups', 'js/ccm.groups.js', array('postprocess' => false));
$al->registerGroup('core/groups', array(
	array('javascript', 'dynatree'),
	array('css', 'dynatree'),
	array('javascript', 'core/groups')
));

// Page Type Compose Form
$al->register('css', 'core/composer', 'css/ccm.composer.css', array('postprocess' => false));
$al->registerGroup('core/composer', array(
	array('css', 'core/composer')
));

// Gathering
$al->register('javascript', 'core/gathering', 'js/ccm.gathering.js');
$al->register('css', 'core/gathering/display', 'css/ccm.gathering.display.css');
$al->register('css', 'core/gathering/base', 'css/ccm.gathering.base.css');
$al->registerGroup('core/gathering', array(
	array('javascript', 'core/gathering'),
	array('css', 'core/gathering/base'),
	array('css', 'core/gathering/display')
));

// Conversation
$al->register('javascript', 'core/conversation', 'js/ccm.conversations.js');
$al->register('css', 'core/conversation', 'css/ccm.conversations.css');
$al->registerGroup('core/conversation', array(
	array('javascript', 'dropzone'),
	array('javascript', 'bootstrap/dropdown'),
	array('javascript', 'core/observer'),
	array('javascript', 'core/conversation'),
	array('css', 'core/conversation'),
	array('css', 'bootstrap/dropdown')
), true);

// Overlay
$al->register('javascript', 'core/overlay', 'js/overlay/jquery.magnific-popup.js');
$al->register('css', 'core/overlay', 'css/overlay/jquery.magnific-popup.css');
$al->registerGroup('core/overlay', array(
	array('javascript', 'core/overlay'),
	array('css', 'core/overlay')
));

// My Account
$al->register('javascript', 'core/account', 'js/ccm.profile.js');
$al->register('css', 'core/account', 'css/ccm.account.css');
$al->registerGroup('core/account', array(
	array('javascript', 'core/account'),
	array('javascript', 'bootstrap/dropdown'),
	array('css', 'bootstrap/dropdown'),
	array('css', 'core/account')
));