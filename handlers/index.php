<?php

$page->add_style (sprintf (
    '<link rel="alternate" type="application/rss+xml" href="http://%s/events/rss" />',
    $_SERVER['HTTP_HOST']
));

if (count ($this->params) > 0 && is_numeric ($this->params[0])) {
    $e = new Event ($this->params[0]);
    if ($e->error || ($e->access !== 'public' && ! User::access ($e->access))) {
        $page->title = __ ('Event not found');
        printf ('<p><a href="/events">&laquo; %s</a></p>', __ ('Back'));

        return;
    }

    $page->id = 'events';
    $page->title = $e->title;
    $page->layout = $appconf['Events']['event_layout'];
    $this->run ('admin/util/minimal-grid');
	$page->add_style ('/apps/events/css/events.css');

    $e->details = $tpl->run_includes ($e->details);
    $e->remaining = $e->available ();
    if ($e->end_date === '' || $e->end_date === '0000-00-00' || $e->end_date === $e->start_date) {
        $e->end_date = false;
    }
    $e->has_passed = ($e->start_date . ' ' . $e->starts) < gmdate ('Y-m-d H:i:s');
    echo $tpl->render ('events/event', $e->orig ());
} else {
	if (! $this->internal) {
		$page->id = 'events';
		$page->title = __ ($appconf['Events']['title']);
		$page->layout = $appconf['Events']['layout'];
	}

	$this->run ('admin/util/minimal-grid');
	$page->add_style ('/apps/events/css/events.css');
	
	if (isset ($this->params[0]) && $this->params[0] === 'category') {
		if (! isset ($this->params[1])) {
			$this->redirect ('/events');
		}
		$category = $this->params[1];
	} else {
		$category = false;
	}
	
	$data = array (
		'limit' => 20,
		'details' => 'yes',
		'events' => array (),
		'category' => $category,
		'categories' => events\Category::query ()->order ('name', 'asc')->fetch_assoc ('id', 'name')
	);

	$start = gmdate ('Y-m-d');

	if ($category) {
		$data['events'] = Event::query ()
			->where (function ($q) {
				$q->where ('access', 'public');
				if (User::require_login ()) {
					$q->or_where ('access', 'member');
				}
			})
			->where ('category', $category)
			->where ('start_date >= "' . $start . '"')
			->order ('start_date', 'asc')
			->fetch_orig ($data['limit']);
	} else {
		$data['events'] = Event::query ()
			->where (function ($q) {
				$q->where ('access', 'public');
				if (User::require_login ()) {
					$q->or_where ('access', 'member');
				}
			})
			->where ('start_date >= "' . $start . '"')
			->order ('start_date', 'asc')
			->fetch_orig ($data['limit']);
	}
	
	$data['events'] = is_array ($data['events']) ? $data['events'] : array ();

	foreach ($data['events'] as $key => $event) {
		$data['events'][$key]->date = $event->start_date . ' ' . $event->starts;
	}
	
	echo $tpl->render ('events/index', $data);
}
