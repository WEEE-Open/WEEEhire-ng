<?php
/** @var string $day */
switch($day) {
	case '1':
		echo __('Lunedì');
		break;
	case '2':
		echo __('Martedì');
		break;
	case '3':
		echo __('Mercoledì');
		break;
	case '4':
		echo __('Giovedì');
		break;
	case '5':
		echo __('Venerdì');
		break;
	case '6':
		echo __('Sabato');
		break;
	case '7':
		echo __('Domenica');
		break;
	default:
		echo '?';
		break;
}
