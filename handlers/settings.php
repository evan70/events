<?php

/**
 * This is the settings form for the blog app.
 */

$this->require_admin ();

require_once ('apps/admin/lib/Functions.php');

$page->layout = 'admin';
$page->title = __ ('Events Settings');

$form = new Form ('post', $this);

$form->data = array (
	'title' => $appconf['Events']['title'],
	'layouts' => admin_get_layouts (),
	'layout' => $appconf['Events']['layout'],
	'event_layout' => $appconf['Events']['event_layout'],
	'gcal_link' => $appconf['Events']['gcal_link']
);

echo $form->handle (function ($form) {
	$settings = Appconf::merge ('events', array (
		'Events' => array (
			'title' => $_POST['title'],
			'layout' => $_POST['layout'],
			'event_layout' => $_POST['event_layout'],
			'gcal_link' => $_POST['gcal_link']
		)
	));

	if (! Ini::write ($settings, 'conf/app.events.' . ELEFANT_ENV . '.php')) {
		printf ('<p>%s</p>', __ ('Unable to save changes. Check your folder permissions and try again.'));
		return;
	}

	$form->controller->add_notification (__ ('Settings saved.'));
	$form->controller->redirect ('/events/admin');
});

?>
