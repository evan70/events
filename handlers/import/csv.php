<?php

/**
 * Implements a blog post importer from a CSV file.
 */

$this->require_acl ('admin', 'events');

$page->layout = 'admin';
$page->title = __ ('CSV Importer');

$f = new Form ('post');

if ($f->submit ()) {
	if (move_uploaded_file ($_FILES['import_file']['tmp_name'], 'cache/events_csv_import.csv')) {
		$file = 'cache/events_csv_import.csv';

		$res = blog\CsvParser::parse ($file);

		if (is_array ($res)) {
			$headers = array_shift ($res);
			$samples = array ();
			for ($i = 0; $i < 3; $i++) {
				if (isset ($res[$i])) {
					$samples[] = $res[$i];
				}
			}

			require_once ('apps/blog/lib/Filters.php');

			echo $tpl->render ('events/import/csv2', array (
				'headers' => $headers,
				'samples' => $samples
			));
			return;
		} else {
			echo '<p><strong>' . __ ('Unable to parse the uploaded file.') . '</strong></p>';
		}
	} else {
		echo '<p><strong>' . __ ('Error uploading file.') . '</strong></p>';
	}
}

$o = new StdClass;

echo $tpl->render ('events/import/csv', $o);
