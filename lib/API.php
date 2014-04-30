<?php

namespace events;

class API extends \Restful {
	/**
	 * Reserve a specified number of tickets.
	 *
	 *     POST /events/api/registration/reserve/<event-id>/<num-attendees>
	 */
	public function post_reserve ($event_id, $num_attendees) {
		if (! \User::require_login ()) {
			return $this->error ('Must be logged in.');
		}

		$registration = Registration::reserve ($event_id, $num_attendees);
		if ($registration->error) {
			return $this->error ('Unable to make a reservation at this time.');
		}
		return $registration->orig ();
	}

	/**
	 * Check if a reservation has expired.
	 *
	 *     GET /events/api/registration/expired/<registration-id>
	 */
	public function get_expired ($registration_id) {
		if (! \User::require_login ()) {
			return $this->error ('Must be logged in.');
		}

		$registration = new Registration ($registration_id);
		if ($registration->error) {
			return $this->error ('Reservation not found.');
		}
		
		return $registration->expired ();
	}

	/**
	 * Update a registration with company name and attendees.
	 *
	 *     POST /events/api/registration/update/<registration-id>
	 *
	 * Parameters:
	 *
	 * - company
	 * - attendees (array of attendee names)
	 */
	public function post_update ($registration_id) {
		if (! \User::require_login ()) {
			return $this->error ('Must be logged in.');
		}

		$registration = new Registration ($registration_id);
		if ($registration->error) {
			return $this->error ('Reservation not found.');
		}
		
		if (isset ($_POST['company'])) {
			$registration->company = $_POST['company'];
		}

		if (isset ($_POST['attendees'])) {
			if (! is_array ($_POST['attendees'])) {
				return $this->error ('Attendees should be an array.');
			}
			if (count ($_POST['attendees']) != $registration->num_attendees) {
				return $this->error ('Incorrect number of attendees.');
			}
			$registration->attendees = json_encode ($_POST['attendees']);
		}

		if (! $registration->put ()) {
			error_log ('[events/api/registration/update] ' . $registration->error);
			return $this->error ('Error saving registration info.');
		}
		return true;
	}

	/**
	 * Mark a registration as completed.
	 *
	 *     POST /events/api/registration/complete/<registration-id>[/<payment-id>]
	 */
	public function post_complete ($registration_id, $payment_id = 0) {
		if (! \User::require_login ()) {
			return $this->error ('Must be logged in.');
		}

		$registration = new Registration ($registration_id);
		if ($registration->error) {
			return $this->error ('Reservation not found.');
		}
		
		return $registration->complete ($payment_id);
	}
}

?>