<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('format_money')) {
	/**
	 * Format money
	 * 
	 * @access public
	 * @param string $value
	 * @param int $decimal
	 * @return string
	 */
	function format_money($value, $decimal = 2)
	{
		if (is_null($value)) {
			return false;
		}

		$string = '';
		$string .= ($value < 0) ? '-' : '';
		$string .= number_format(round(abs($value), (int)$decimal), (int)$decimal, ',', '.');

		return $string;
	}
}

if (!function_exists('format_currency')) {
	/**
	 * Format currency
	 * 
	 * @access public
	 * @param string $float
	 * @param bool $format
	 * @return string
	 */
	function format_currency($float, $format = false)
	{
		if (is_null($float)) {
			return false;
		}

		$ci = &get_instance();
		$ci->load->library('localisation/currency');

		return $ci->currency->format($float, '', '', $format);
	}
}

if (!function_exists('format_date')) {
	/**
	 * Format date
	 * 
	 * @access public
	 * @param string $string
	 * @param bool $time
	 * @return string
	 */
	function format_date($string, $time = false)
	{
		$format = $time ? 'd/m/Y H:i' : 'd/m/Y';

		if ($string == '0000-00-00 00:00:00' || $string == '0000-00-00') {
			return '';
		}

		return date($format, strtotime($string));
	}
}

if (!function_exists('format_doc_number')) {
	/**
	 * Format document number
	 * Create document number from prefix and ID 
	 * 
	 * @access public
	 * @param string $type
	 * @param int $id
	 * @return string
	 */
	function format_doc_number($type, $id = 0)
	{
		$ci = &get_instance();
		$ci->load->config('numbering');

		$prefixs = $ci->config->item('prefix_no');

		if (array_key_exists($type, $prefixs)) {
			$number = sprintf('%07d', $id);
			return $prefixs[$type] . (string)$number;
		}
	}
}

if (!function_exists('format_trouble_no')) {
	function format_trouble_no()
	{
		$purchase_code = 'TRB-' . date('dmY') . mt_rand(1000, 9999);  // better than rand()

		// call the same function if the barcode exists already
		if (trouble_no_exist($purchase_code)) {
			return format_trouble_no();
		}

		// otherwise, it's valid and can be used
		return $purchase_code;
	}
}

if (!function_exists('trouble_no_exist')) {
	function trouble_no_exist($purchase_code)
	{
		$ci = &get_instance();
		$ci->load->model("Model_trouble");

		return $ci->Model_trouble->get_by_custom("trouble_no", $purchase_code);
		// return Purchase_order::withTrashed()->where('purchase_order_code', $purchase_code)->exists();

	}
}

if (!function_exists('customer_code')) {
	function customer_code()
	{
		$purchase_code = 'CS' . date('dmY') . mt_rand(100, 999);  // better than rand()

		// call the same function if the barcode exists already
		if (customer_code_exist($purchase_code)) {
			return customer_code();
		}

		// otherwise, it's valid and can be used
		return $purchase_code;
	}
}

if (!function_exists('customer_code_exist')) {
	function customer_code_exist($purchase_code)
	{
		$ci = &get_instance();
		$ci->load->model("Model_customer");

		return $ci->Model_customer->get_by_custom("customer_code", $purchase_code);
		// return Purchase_order::withTrashed()->where('purchase_order_code', $purchase_code)->exists();

	}
}

if (!function_exists('area_code')) {
	function area_code()
	{
		$purchase_code = 'AC' . date('dmY') . mt_rand(100, 999);  // better than rand()

		// call the same function if the barcode exists already
		if (area_code_exist($purchase_code)) {
			return area_code();
		}

		// otherwise, it's valid and can be used
		return $purchase_code;
	}
}

if (!function_exists('area_code_exist')) {
	function area_code_exist($purchase_code)
	{
		$ci = &get_instance();
		$ci->load->model("Model_area");

		return $ci->Model_area->get_by_custom("area_code", $purchase_code);
		// return Purchase_order::withTrashed()->where('purchase_order_code', $purchase_code)->exists();

	}
}

if (!function_exists('format_address')) {
	/**
	 * Format address
	 * 
	 * @access public
	 * @param array $address
	 * @return string
	 */
	function format_address($address)
	{
		$find = array('{name}', '{address}', '{telephone}', '{city}', '{province}');

		$replace = array(
			'name'		=> $address['name'],
			'address'	=> $address['address'],
			'telephone'	=> $address['telephone'],
			'city'	=> $address['city'],
			'province'	=> $address['province']
		);

		$format = '{name}' . "\n" . '{address}, {city} - {province}' . "\n" . '{telephone}';

		return str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
	}
}

if (!function_exists('get_number')) {
	/**
	 * Get number
	 * 
	 * @access public
	 * @param string $doc_number
	 * @return int|bool
	 */
	function get_number($doc_number)
	{
		preg_match('/([a-zA-Z]+)(\d+)/', $doc_number, $matches);

		if (count($matches) == 3) {
			return (int)$matches[2];
		}

		return false;
	}
}

if (!function_exists('check_date_format')) {
	/**
	 * Check date format
	 * 
	 * @access public
	 * @param string $date
	 * @return void
	 */
	function check_date_format($date)
	{
		if (preg_match('/^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/', $date)) {
			return true;
		}

		return false;
	}
}

if (!function_exists('format_serial')) {
	/**
	 * Format serial
	 * 
	 * @access public
	 * @param string $serial
	 * @param string $digit_split
	 * @return string
	 */
	function format_serial($serial, $digit_split = 4)
	{
		return implode('-', str_split($digit_split, 4));
	}
}

if (!function_exists('format_tax_number')) {
	/**
	 * Format tax number
	 * 
	 * @access public
	 * @param string $tax_number
	 * @return string
	 */
	function format_tax_number($tax_number)
	{
		return $tax_number;
	}
}

if (!function_exists('to_word')) {
	/**
	 * To word for Indonesian
	 * 
	 * @access private
	 * @param float $num
	 * @return string
	 */
	function to_word($num)
	{
		$num = abs($num);
		$words = array('', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas');
		$temp = ' ';

		if ($num < 12) {
			$temp = ' ' . $words[$num];
		} elseif ($num < 20) {
			$temp = to_word($num - 10) . ' belas';
		} elseif ($num < 100) {
			$temp = to_word($num / 10) . ' puluh' . to_word($num % 10);
		} elseif ($num < 200) {
			$temp = ' seratus' . to_word($num - 100);
		} elseif ($num < 1000) {
			$temp = to_word($num / 100) . ' ratus' . to_word($num % 100);
		} elseif ($num < 2000) {
			$temp = ' seribu' . to_word($num - 1000);
		} elseif ($num < 1000000) {
			$temp = to_word($num / 1000) . ' ribu' . to_word($num % 1000);
		} elseif ($num < 1000000000) {
			$temp = to_word($num / 1000000) . ' juta' . to_word($num % 1000000);
		} elseif ($num < 1000000000000) {
			$temp = to_word($num / 1000000000) . ' milyar' . to_word(fmod($num, 1000000000));
		} elseif ($num < 1000000000000000) {
			$temp = to_word($num / 1000000000000) . ' trilyun' . to_word(fmod($num, 1000000000000));
		}

		return $temp;
	}
}

if (!function_exists('in_words')) {
	/**
	 * Inwords
	 * 
	 * @access public
	 * @param float $num
	 * @param int $style
	 * @return string
	 */
	function in_words($num, $style = null)
	{
		if ($num < 0) {
			$result = 'minus ' . trim(to_word($num));
		} else {
			$poin = trim(to_comma($num));
			$result = trim(to_word($num));
		}

		switch ($style) {
			case 1:
				$result = $poin
					? strtoupper($result) . ' KOMA ' . strtoupper($poin)
					: strtoupper($result);
				break;

			case 2:
				$result = $poin
					? strtolower($result) . ' koma ' . strtolower($poin)
					: strtolower($result);
				break;

			case 3:
				$result = $poin
					? ucwords($result) . ' Koma ' . ucwords($poin)
					: ucwords($result);
				break;

			default:
				$result = $poin
					? ucfirst($result) . ' koma ' . ucfirst($poin)
					: ucfirst($result);
				break;
		}

		return $result;
	}
}

if (!function_exists('to_comma')) {
	/**
	 * To comma
	 * 
	 * @access private
	 * @param float $num
	 * @return string
	 */
	function to_comma($num)
	{
		$num = stristr($num, '.');
		$numbers = array('nol', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan');
		$temp = ' ';
		$length = strlen($num);
		$pos = 1;

		while ($pos < $length) {
			$char = substr($num, $pos, 1);
			$pos++;
			$temp .= ' ' . $numbers[$char];
		}

		return $temp;
	}
}

if (!function_exists('substrwords')) {
	/**
	 * Cut string
	 * 
	 * @access public
	 * @param string $text
	 * @param string $maxchar
	 * @param string $end
	 * @return string
	 */
	function substrwords($text, $maxchar = 300, $end = '...')
	{
		if ($text == '') {
			return false;
		}

		if (strlen($text) > $maxchar || $text == '') {
			$words = preg_split('/\s/', $text);
			$output = '';
			$i = 0;

			while (1) {
				$length = strlen($output) + strlen($words[$i]);

				if ($length > $maxchar) {
					break;
				} else {
					$output .= " " . $words[$i];
					++$i;
				}
			}

			$output .= $end;
		} else {
			$output = $text;
		}
		return $output;
	}
}

if (!function_exists('format_time_ago')) {
	function format_time_ago($datetime)
	{
		$datetime1 = new DateTime('now');
		$datetime2 = new DateTime($datetime);

		$interval = $datetime1->diff($datetime2);
		$suffix = ($interval->invert ? ' ago' : '');

		if ($v = $interval->y >= 1) return pluralize($interval->y, 'year') . $suffix;
		if ($v = $interval->m >= 1) return pluralize($interval->m, 'month') . $suffix;
		if ($v = $interval->d >= 1) return pluralize($interval->d, 'day') . $suffix;
		if ($v = $interval->h >= 1) return pluralize($interval->h, 'hour') . $suffix;
		if ($v = $interval->i >= 1) return pluralize($interval->i, 'minute') . $suffix;

		return pluralize($interval->s, 'second') . $suffix;
	}
}

if (!function_exists('pluralize')) {
	function pluralize($count, $text)
	{
		return $count . (($count == 1) ? (" $text") : (" ${text}s"));
	}
}

if (!function_exists('format_image')) {
	function format_image($image = 'no_image.jpg', $width = 0, $height = 0)
	{
		$_ci = &get_instance();

		if (!class_exists('image')) {
			$_ci->load->library('image');
		}

		return $_ci->image->resize($image, $width, $height);
	}
}

if (!function_exists('format_minute')) {
	function format_minute($minute)
	{
		if ((int)$minute < 1) {
			return false;
		}

		$h = floor($minute / 60) ? pluralize(floor($minute / 60), 'hour') : '';
		$m = $minute % 60 ? pluralize($minute % 60, 'minute') : '';
		return $h && $m ? $h . ' and ' . $m : $h . $m;
	}
}

if (!function_exists('short_url')) {
	function short_url($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://is.gd/create.php?format=simple&url=' . urlencode($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
}


if (!function_exists('time_ago')) {
	function time_ago_set($time)
	{
		$time = time() - $time; // to get the time since that moment

		$tokens = array(
			31536000 => 'year',
			2592000 => 'month',
			604800 => 'week',
			86400 => 'day',
			3600 => 'hour',
			60 => 'minute',
			1 => 'second'
		);

		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
			$numberOfUnits = floor($time / $unit);
			return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
		}
	}
	function time_ago($date_time_string)
	{
		$time = strtotime($date_time_string);
		return time_ago_set($time) . ' ago';
	}
}

if (!function_exists('format_budget')) {
	function format_budget($budget_min, $budget_max)
	{
		$budget = 0;

		// jika sama berarti fixed budget (custom budget)
		if ((float)$budget_min == (float)$budget_max) {
			$budget = format_money($budget_min, 0);
			// hanya pakai maximum budget (contoh max 500rb)
		} elseif ((float)$budget_min == 0 && (float)$budget_max > 0) {
			$budget = '< ' . format_money($budget_max, 0);
			// hanya pakai minimum budget (contoh 2jt atau lebih)
		} elseif ((float)$budget_min > 0 && (float)$budget_max == 0) {
			$budget = format_money($budget_min, 0) . ' atau lebih';
			// default
		} else {
			$budget = format_money($budget_min, 0) . ' - ' . format_money($budget_max, 0);
		}

		return $budget;
	}
}

if (!function_exists('remove_contact')) {
	function remove_contact($words)
	{
		return $words;
		$replacement = "[xxxxx]";

		//remove phone number
		$pattern = '/(\d{3,})/';
		$words = preg_replace($pattern, $replacement, $words);

		//remove email 
		$pattern = "/[^@\s]*@[^@\s]*\.[^@\s]*/";
		$words = preg_replace($pattern, $replacement, $words);

		//remove url
		$pattern = "/[a-zA-Z]*[:\/\/]*[A-Za-z0-9\-_]+\.+[A-Za-z0-9\.\/%&=\?\-_]+/i";
		$words = preg_replace($pattern, $replacement, $words);

		return $words;
	}
}


// if (!function_exists('get_youtube_id')) {
// 	function get_youtube_id($url)
// 	{
// 		// Get image form video URL
// 		// $url = 'https://www.youtube.com/watch?v=k_GM1JA608Y';

// 		$urls = parse_url($url);

// 		//Expect the URL to be http://youtu.be/abcd, where abcd is the video ID
// 		if ($urls['host'] == 'youtu.be') :

// 			$imgPath = ltrim($urls['path'], '/');

// 		//Expect the URL to be http://www.youtube.com/embed/abcd
// 		elseif (strpos($urls['path'], 'embed') == 1) :

// 			$imgPath = end(explode('/', $urls['path']));

// 		//Expect the URL to be abcd only
// 		elseif (strpos($url, '/') === false) :

// 			$imgPath = $url;

// 		//Expect the URL to be http://www.youtube.com/watch?v=abcd
// 		else :

// 			parse_str($urls['query']);

// 			$imgPath = [];

// 		endif;
// 		return $imgPath;
// 	}
// }

if (!function_exists('badge_pending_order')) {
	function badge_pending_order()
	{
		$CI = &get_instance();

		$CI->load->model('order_model');

		return $CI->order_model->get_pending_order($CI->session->userdata('restaurant_id'));
	}
}

if (!function_exists('badge_confirmed_order')) {
	function badge_confirmed_order()
	{
		$CI = &get_instance();

		$CI->load->model('order_model');

		return $CI->order_model->get_confirmed_order($CI->session->userdata('restaurant_id'));
	}
}
if (!function_exists('badge_onseat_order')) {
	function badge_onseat_order()
	{
		$CI = &get_instance();

		$CI->load->model('order_model');

		return $CI->order_model->get_onseat_order($CI->session->userdata('restaurant_id'));
	}
}

if (!function_exists('badge_total_order')) {
	function badge_total_order()
	{
		$CI = &get_instance();

		return count(badge_pending_order()) + count(badge_confirmed_order());
	}
}



if (!function_exists('icon_dolar')) {
	function icon_dolar($start_from)
	{
		if ($start_from <= 35000) {
			return '<span class="icon-price active"><i class="fa fa-dollar"></i></i></span><span class="icon-price"><i class="fa fa-dollar"></i></i></span><span class="icon-price"><i class="fa fa-dollar"></i></i></span><span class="icon-price"><i class="fa fa-dollar"></i></i></span>';
		} else if ($start_from <= 75000) {
			return '<span class="icon-price active"><i class="fa fa-dollar"></i></i></span><span class="icon-price active"><i class="fa fa-dollar"></i></i></span><span class="icon-price"><i class="fa fa-dollar"></i></i></span><span class="icon-price"><i class="fa fa-dollar"></i></i></span>';
		} else if ($start_from <= 150000) {
			return '<span class="icon-price active"><i class="fa fa-dollar"></i></i></span><span class="icon-price active"><i class="fa fa-dollar"></i></i></span><span class="icon-price active"><i class="fa fa-dollar"></i></i></span><span class="icon-price"><i class="fa fa-dollar"></i></i></span>';
		} else {
			return '<span class="icon-price active"><i class="fa fa-dollar"></i></i></span><span class="icon-price active"><i class="fa fa-dollar"></i></i></span><span class="icon-price active"><i class="fa fa-dollar"></i></i></span><span class="icon-price active"><i class="fa fa-dollar"></i></i></span>';
		}
	}
}

if (!function_exists('bulan3')) {
	function bulan3($nomer)
	{
		$bulan = array(
			1 =>   'Jan',
			'Feb',
			'Mar',
			'Apr',
			'Mei',
			'Jun',
			'Jul',
			'Agu',
			'Sep',
			'Okt',
			'Nov',
			'Des'
		);
		return $bulan[(int)$nomer];
	}
}

if (!function_exists('tanggal_indo')) {
	function tanggal_indo($date)
	{
		$bulan = array(
			1 =>   'Jan',
			'Feb',
			'Mar',
			'Apr',
			'Mei',
			'Jun',
			'Jul',
			'Agu',
			'Sep',
			'Okt',
			'Nov',
			'Des'
		);
		$nomer = date('n', strtotime($date));

		return date('d ', strtotime($date)) . $bulan[(int)$nomer] . date(' Y', strtotime($date));
	}
}

if (!function_exists('tanggal_indo_full')) {
	function tanggal_indo_full($date)
	{
		$bulan = array(
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$nomer = date('n', strtotime($date));

		return date('d ', strtotime($date)) . $bulan[(int)$nomer] . date(' Y', strtotime($date));
	}
}

if (!function_exists('tgl_bulan')) {
	function tgl_bulan($date)
	{
		$bulan = array(
			1 =>   'Jan',
			'Feb',
			'Mar',
			'Apr',
			'Mei',
			'Jun',
			'Jul',
			'Agu',
			'Sep',
			'Okt',
			'Nov',
			'Des'
		);
		$nomer = date('n', strtotime($date));

		return date('d ', strtotime($date)) . $bulan[(int)$nomer];
	}
}

if (!function_exists('jam_tanggal_indo')) {
	function jam_tanggal_indo($date_time)
	{
		$bulan = array(
			1 =>   'Jan',
			'Feb',
			'Mar',
			'Apr',
			'Mei',
			'Jun',
			'Jul',
			'Agu',
			'Sep',
			'Okt',
			'Nov',
			'Des'
		);
		$nomer = date('m', strtotime($date_time));

		return date('d ', strtotime($date_time)) . $bulan[(int)$nomer] . date(' Y, H.i', strtotime($date_time));
	}
}
if (!function_exists('bulan_indo')) {
	function bulan_indo($tanggal)
	{
		$bulan = array(
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$angka = date('n', strtotime($tanggal));

		return date('d', strtotime($tanggal)) . ' ' . $bulan[(int)$angka];
	}
}
if (!function_exists('hari_ini')) {
	function hari_ini($date)
	{
		$hari = date("D", strtotime($date));

		switch ($hari) {
			case 'Sun':
				$hari_ini = "Minggu";
				break;

			case 'Mon':
				$hari_ini = "Senin";
				break;

			case 'Tue':
				$hari_ini = "Selasa";
				break;

			case 'Wed':
				$hari_ini = "Rabu";
				break;

			case 'Thu':
				$hari_ini = "Kamis";
				break;

			case 'Fri':
				$hari_ini = "Jumat";
				break;

			case 'Sat':
				$hari_ini = "Sabtu";
				break;

			default:
				$hari_ini = "Tidak di ketahui";
				break;
		}

		return $hari_ini;
	}
}


if (!function_exists('class_active')) {
	function class_active($controller, $class_name)
	{
		if ($controller == $class_name) {
			return 'active';
		}
	}
}

if (!function_exists('no_order')) {
	function no_order()
	{
		$today = date("Ymd");
		$rand = strtoupper(substr(uniqid(sha1(time())), 0, 4));
		$order_no = 'INV' . str_replace("-", "", $today) . $rand;

		return $order_no;
	}
}

if (!function_exists('account_type')) {
	function account_type()
	{
		$ci = &get_instance();
		$user_id  = $ci->session->userdata('user_id');
		if (!$user_id) {
			return false;
		}
		$ci->load->model('Model_transaction');

		$last_trx = $ci->Model_transaction->last_trx($user_id);
		$now = date('Y-m-d H:i:s');
		$expired = $last_trx['date_end'];
		if ($now > $expired || $last_trx == null) {
			return [];
		} else {
			$product_id = $last_trx['product_id'];
		}

		return $last_trx;
	}

	if (!function_exists('available_upload')) {
		function available_upload()
		{
			//hardcode dlu karna gaada trigger-nya
			$products = [8, 9, 10, 11];
			return $products;
		}
	}

	if (!function_exists('message_share')) {
		function message_share($receiver = null, $url = null, $bridegroom = array(), $type = 1)
		{
			if ($receiver == null || $url == null) {
				return false;
			}


			$fullname_man = isset($bridegroom['fullname_man']) ? $bridegroom['fullname_man'] : null;
			$fullname_girl = isset($bridegroom['fullname_girl']) ? $bridegroom['fullname_girl'] : null;
			$nickname_man = isset($bridegroom['nickname_man']) ? $bridegroom['nickname_man'] : null;
			$nickname_girl = isset($bridegroom['nickname_girl']) ? $bridegroom['nickname_girl'] : null;

			if ($type == 1) { //copy to clipboard
				$message = "Bismillahirrahmanirrahim,<br/>Assalamu'alaikum Warahmatullah Wabarakatuh<br/><br/>Dengan Memohon Rahmat dan Ridho Allah Subhanawata'ala, Perkenankan kami mengundang Bapak/Ibu/Saudara/i " . $receiver . " untuk menghadiri acara pernikahan kami :<br/></br>*" . $fullname_man . " dan " . $fullname_girl . "*<br/></br>Berikut link untuk info lengkap dari acara kami :<br/>" . $url . "<br/><br/>Merupakan suatu kebahagiaan bagi kami apabila Saudara/i berkenan untuk hadir dan memberikan doa restu.<br/><br/>Wassalamu'alaikum Warahmatullah Wabarakatuh<br/></br>🌹 " . $nickname_man . " dan " . $nickname_girl . " 🌹";
			} else { //whatsapp
				$message = "Bismillahirrahmanirrahim%2C%0AAssalamu%27alaikum%20Warahmatullah%20Wabarakatuh%0A%0ADengan%20Memohon%20Rahmat%20dan%20Ridho%20Allah%20Subhanawata%27ala%2C%20Perkenankan%20kami%20mengundang%20Bapak%2FIbu%2FSaudara%2Fi%20" . $receiver . "%20untuk%20menghadiri%20acara%20pernikahan%20kami%20%3A%0A%0A*" . $fullname_man . "dan " . $fullname_girl . "*%0A%0ABerikut%20link%20untuk%20info%20lengkap%20dari%20acara%20kami%20%3A%0A" . $url . "%0A%0AMerupakan%20suatu%20kebahagiaan%20bagi%20kami%20apabila%20Saudara%2Fi%20berkenan%20untuk%20hadir%20dan%20memberikan%20doa%20restu.%0A%0AWassalamu%27alaikum%20Warahmatullah%20Wabarakatuh%0A%0A%F0%9F%8C%B9%20" . $nickname_man . "%20dan%20" . $nickname_girl . "%20%F0%9F%8C%B9";
			}

			// $message = "_Assalamu'alaikum Warahmatullahi Wabarakaatuh_ <br/> Tanpa mengurangi rasa hormat, izinkan kami mengundang Bapak/Ibu/Saudara/i *" . $receiver . "* untuk hadir serta memberikan do'a restu pada acara pernikahan kami. <br> Untuk detail acara, lokasi, dan ucapan bisa klik link dibawah ini: <br/>" .  $url . " Merupakan suatu kehormatan dan kebahagiaan bagi kami, apabila Bapak/Ibu/Saudara/i " . $receiver . " berkenan hadir.<br/><br/> Do'a restu Anda merupakan hadiah terindah bagi kami. Atas kehadiran dan do'a restu yang telah diberikan, kami ucapkan terima kasih. <br/><br/> _Wassalamu'alaikum Warahmatullahi Wabarakatuh._";

			// $final_message = str_replace('\r\n', '%0A%0A', $message);
			return $message;
		}
	}
}

if (!function_exists('not_found')) {
	function not_found()
	{
		// redirect('show_404');
		$ci = &get_instance();
		$ci->load->view('404');
	}
}

if (!function_exists('upload_files')) {
	function upload_files($path, $title, $files)
	{
		$ci = &get_instance();

		$config = array(
			'upload_path'   => $path,
			'allowed_types' => 'jpg|gif|png',
			'overwrite'     => 1,
		);

		$ci->load->library('upload', $config);

		$images = array();

		foreach ($files['name'] as $key => $image) {
			$_FILES['upload_images[]']['name'] = $files['name'][$key];
			$_FILES['upload_images[]']['type'] = $files['type'][$key];
			$_FILES['upload_images[]']['tmp_name'] = $files['tmp_name'][$key];
			$_FILES['upload_images[]']['error'] = $files['error'][$key];
			$_FILES['upload_images[]']['size'] = $files['size'][$key];

			$fileName = $title . '_' . $image;

			$upload_images[] = $fileName;

			$config['file_name'] = $fileName;

			$ci->upload->initialize($config);

			if ($ci->upload->do_upload('upload_images[]')) {
				$ci->upload->data();
			} else {
				return false;
			}
		}

		return $images;
	}
}

if (!function_exists('getongkir')) {
	function getongkir($customer_code, $origin, $destination, $kilo, $service_type_code)
	{
		$ci = &get_instance();
		$ci->load->library('curl');

		$typePriceCustomer = pricetype($customer_code);

		$ongkir = 0;
		$pricelist = [];
		if ($typePriceCustomer) {
			$url = 'master/shipment_cost_special/special';
			if ($typePriceCustomer->price_flag == 1) {
				$url = 'master/shipment_cost/publish';
			}

			$data = [
				'origin' => $origin,
				'destination' => $destination,
				// 'customer_code' => $customer_code,
				'weight' => $kilo,
				"cost_type" => "NEW"
			];
			$shipment_costs = $ci->curl->sendApiCoreSys("POST", $url, $data);

			if (isset($shipment_costs->price)) {
				$pricelist = $shipment_costs;
				foreach ($shipment_costs->price as $key => $value) {
					if ($key == $service_type_code) {
						$pricelist->finalongkir = $value;
					}
				}
			}
		}

		return $pricelist;
		// return (int)$ongkir;
	}
}

if (!function_exists('pricetype')) {
	function pricetype($customer_code)
	{
		$ci = &get_instance();

		$ci->load->library('curl');

		$pricetype = $ci->curl->sendCurl('GET', 'customer/price_type?customer_code=' . $customer_code . '');

		$return = false;
		if (isset($pricetype->status) && $pricetype->status == 'success') {
			$return = $pricetype->data;
		}

		return $return;
	}
}

if (!function_exists('encrypt_url')) {
	function encrypt_url($string)
	{
		$key = "MAL_979805"; //key to encrypt and decrypts.
		$result = '';
		$test = "";
		for ($i = 0; $i < strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($key, ($i % strlen($key)) - 1, 1);
			$char = chr(ord($char) + ord($keychar));

			$test[$char] = ord($char) + ord($keychar);
			$result .= $char;
		}

		return urlencode(base64_encode($result));
	}
}

if (!function_exists('roundup_price')) {
	function roundup_price($value, $places)
	{
		$mult = pow(10, abs($places));
		return $places < 0 ?
			ceil($value / $mult) * $mult :
			ceil($value * $mult) / $mult;
	}
}

if (!function_exists('customer_available_print_price')) {
	function customer_available_print_price($customer_code)
	{
		// type //
		// 

		// 1 == di bukan print sesuai FRP
		// 2 == hanya shipment publish saja
		$customers = [
			[
				"customer_code" => "CGK052109",
				"type" => "2"
			],
			[
				"customer_code" => "CGK052111",
				"type" => "2"
			],
			[
				"customer_code" => "CGK052113",
				"type" => "2"
			],
			[
				"customer_code" => "CGK052115",
				"type" => "2"
			],
			[
				"customer_code" => "DEV000",
				"type" => "2"
			],
			[
				"customer_code" => "DEV01",
				"type" => "2"
			],
			[
				"customer_code" => "CGKN02222",
				"type" => "2"
			],
			[
				"customer_code" => "CGK053722",
				"type" => "1"
			]
		];

		$result = [];
		foreach ($customers as $key => $value) {
			if ($customer_code == $value['customer_code']) {
				$result = $value;
			}
		}

		return $result;
	}
}

if (!function_exists('convert_imageurl_to_base64')) {
	function convert_imageurl_to_base64($url = '')
	{
		$type               = pathinfo($url, PATHINFO_EXTENSION);
		$data               = @file_get_contents($url);


		if ($data !== false) {
			$data_base64 = array('type' => $type, 'base_64_photo' => 'data:image/' . $type . ';base64,' . base64_encode($data));
			// $base64_photo   = 'data:image/' . $type . ';base64,' . base64_encode($data);
		} else {
			$data_base64 = array('type' => 'png', 'base_64_photo' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
		}


		return $data_base64;
	}
}


if (!function_exists('get_base64image_by_app_version_pod')) {
	function get_base64image_by_app_version_pod($images, $pod_datetime)
	{
		$img_photo = array();

		if (is_array($images)) {
			foreach ($images as $key => $image) {

				// #migrasi aws
				if ($pod_datetime < '2021-03-13 03:19') {
					// telkom
					$image->fullpath_image = str_replace("http://mobileapi.coresyssap.com/", "http://file.mobileapi.coresyssap.com/", $image->fullpath_image);
				}


				$url_public_image   = $image->fullpath_image . $image->image_name;

				$base64_image       = convert_imageurl_to_base64($url_public_image);
				$img_photo[]        = $base64_image;
			}
		} else {
			// #migrasi aws
			if ($pod_datetime < '2021-03-13 03:19') {
				// telkom
				$images = str_replace("http://mobileapi.coresyssap.com/", "http://file.mobileapi.coresyssap.com/", $images);
			}

			$url_public_image   = $images;

			$base64_image       = convert_imageurl_to_base64($url_public_image);
			$img_photo[]        = $base64_image;
		}

		return $img_photo;
		// return $url_public_image;
	}


	if (!function_exists('listmenu')) {
		function listmenu()
		{
			$ci = &get_instance();
			$ci->load->model('Model_menu');
			$get_management_menu = $ci->Model_menu->get_by_custom_all("is_master", 1);
			$management_menu = (array) $get_management_menu;
			foreach ($management_menu as $key => $value) {
				$submenu = $ci->Model_menu->get_by_custom_all("parent_id", $value->menu_id);
				$value->submenu = json_decode(json_encode($submenu, true), true);
				$management_menu[$key] = (array)$value;
			};

			return $management_menu;
		}
	}


	if (!function_exists('management_access')) {
		function management_access()
		{
			$ci = &get_instance();
			$ci->load->model('Model_roles');
			$get_management_menu = $ci->Model_roles->get_all();
			$management_menu = (array) $get_management_menu;
			foreach ($management_menu as $key => $value) {
				$management_menu[$key] = (array)$value;
			}
			return $management_menu;
		}
	}
	if (!function_exists('user_access')) {
		function user_access()
		{
			$ci = &get_instance();
			// $user_id = $ci->session->userdata("user_id");
			$roles_id = $ci->session->userdata("level");
			// $roles_id = 30;

			$management_menu = management_access();

			$access_menu = [];
			foreach ($management_menu as $key => $value) {
				if ($roles_id == $value["roles_id"]) {
					$access_menu = explode(",", $value["menu_access"]);
				}
			}

			return $access_menu;
		}
	}


	if (!function_exists('is_valid_access')) {
		function is_valid_access()
		{
			$menus = listmenu();
			$current_url = current_url();
			$acces_menu = user_access();
			$url_master = [];
			$url_sub = [];
			foreach ($menus as $key => $value) {
				if (!in_array($value['menu_id'], $acces_menu)) {
					unset($menus[$key]);
				} else {
					foreach ($value["submenu"] as $k => $v) {
						if (!in_array($v['menu_id'], $acces_menu)) {
							unset($value["submenu"][$k]);
						}

						if (in_array($v['menu_id'], $acces_menu)) {
							$url_sub[] = $v["url"];
						}
					}

					$menus[$key] = $value;
					$url_master[] = $value["url"];
				}
			}

			$url_master = array_filter($url_master);
			$url_sub = array_filter($url_sub);
			$final_access = array_merge($url_master, $url_sub);

			if (!in_array($current_url, $final_access)) {
				return false;
			} else {
				return true;
			}
		}
	}
}

if (!function_exists('level_user')) {
	function level_user($level)
	{
		if ($level == 1) {
			$label = 'Super Admin';
		} else if ($level == 2) {
			$label = 'Admin';
		} else {
			$label = 'Technicians';
		}
		return $label;
	}
}

if (!function_exists('get_distance')) {
	function get_distance($trouble_no)
	{
		if (!$trouble_no) {
			return false;
		}


		//cek trouble nya ...
		$ci = &get_instance();
		$ci->load->model('Model_trouble');
		$ci->load->model('Model_area');

		$trouble = $ci->Model_trouble->get_by($trouble_no);

		if (!$trouble) {
			return false;
		}

		//get location basecamps
		$areas = $ci->Model_area->get_by_custom("area_code", $trouble->area_code);
		if (!$areas) {
			return false;
		}

		return calculate_distance($areas->lat, $areas->long, $trouble->lat, $trouble->long, 'K');
	}
}

if (!function_exists('calculate_distance')) {
	function calculate_distance(
		$latitudeFrom,
		$longitudeFrom,
		$latitudeTo,
		$longitudeTo,
		$unit
	) {

		$theta = $longitudeFrom - $longitudeTo;
		$dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		if ($unit == "K") {
			return ($miles * 1.609344);
		} else if ($unit == "N") {
			return ($miles * 0.8684);
		} else {
			return $miles;
		}
		// // convert from degrees to radians
	}
}

if (!function_exists('photo_technician')) {
	function photo_technician($technician_id)
	{
		if ($technician_id == null) {
			$avatar = base_url('assets/media/svg/avatars/blank.svg');
			return $avatar;
		}

		// $user_id = Auth::user()->user_id;
		$ci = &get_instance();
		$ci->load->model('Model_technician');
		$getphoto = $ci->Model_technician->get_by($technician_id);

		$avatar = base_url('assets/media/svg/avatars/blank.svg');
		if ($getphoto->user_image != null) {
			$avatar = $getphoto->user_image;
		}

		return $avatar;
	}
}

if (!function_exists('max_range_date_export')) {
	function max_range_date_export($from, $to)
	{
		$f_ymd = date('Y-m-d', strtotime($from));
		$t_ymd = date('Y-m-d', strtotime($to));

		$date1 = new DateTime($f_ymd);
		$date2 = new DateTime($t_ymd);
		$interval = $date1->diff($date2);
		$limit = 31;

		// if($interval->d > $limit){ // -> hny bs calcuate daynya cuma 1 bulan
		if ($interval->days > $limit) { // -> bisa calculate daysnya > 1 bulan	
			return true;
		} else {
			return false;
		}
	}
}

if (!function_exists('packing_cost')) {
	function packing_cost($packing_type_code, $volumetric, $kilo, $contract_id)
	{
		$ci = &get_instance();

		if ($packing_type_code == 'ACH02') {
			if ($kilo <= '4') {
				$packing_cost = '25000';
			} else if ($kilo > '4' && $kilo <= '20') {
				$packing_cost = 6000 * $kilo;
			} else {
				$packing_cost = 120000 + (($kilo - 20) * 3000);
			}
		} else if ($packing_type_code == 'ACH05') {
			if ($kilo <= '4') {
				$packing_cost = '5000';
			} else {
				$packing_cost = 1000 * $kilo;
			}
		} else if ($packing_type_code == 'ACH04') {
			if ($kilo <= '4') {
				$packing_cost = '28000';
			} else if ($kilo > '4' && $kilo <= '20') {
				$packing_cost = 7000 * $kilo;
			} else {
				$packing_cost = 140000 + (($kilo - 20) * 4000);
			}
		} else if ($packing_type_code == 'ACH03') {
			$packing_cost = '11000';
		} else if ($packing_type_code == 'ACH06') {
			$packing_cost = '6500';
		} else {
			$packing_cost = '0';
		}

		//cek packing cost di contract customize or not //
		if ($contract_id) {
			$packing_contract = $ci->db->get_where("tb_packing_type_contract", ["packing_type_code" => $packing_type_code, "contract_id" => $contract_id])->row();
			if ($packing_contract) {
				$additional_cost = $packing_contract->additinonal_cost;
				$volumetric = explode("x", $volumetric);
				$p = $volumetric[0];
				$l = $volumetric[1];
				$t = $volumetric[2];
				$packing_cost = ($p + $l + $t) / 3 * $additional_cost;
			}
		}

		return trim(ceil($packing_cost));
	}
}

if (!function_exists('volumetrict_kg')) {
	function volumetrict_kg($volume, $kilo_divider)
	{
		$volumetric = explode("x", $volume);

		$p = $volumetric[0];
		$l = $volumetric[1];
		$t = $volumetric[2];

		$volumetric_kg = round(($p * $l * $t) / $kilo_divider);
		return round_kg($volumetric_kg);
	}
}

if (!function_exists('round_kg')) {
	function round_kg($n)
	{
		if ($n < 1) {
			return 1; // 1kg
		}

		$fraction   = fmod($n, 1); // 1.25 -> 0.25 (get whole decimal)
		$round      = 0.3;
		$precision  = 5; // handling to comparing two float number

		// 0.3 >= 0.3
		if (round($fraction, $precision) >= round($round, $precision)) {
			$rounded_kg = ceil($n); // round up
		} else {
			$rounded_kg = floor($n); // round down
		}

		return $rounded_kg;
	}
}

if (!function_exists('clean_string')) {
	function clean_string($string)
	{
		// $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
		// $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
		$string = preg_replace('/[^A-Za-z0-9 ]/', '', $string); // Removes special chars.

		return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
	}
}

if (!function_exists('provide_rowstate')) {
	function provide_rowstate()
	{
		$list_rowstate_show = array(
			// 1,  // BELUM MANIFEST PICKUP
			2,  // ENTRI
			3,  // ENTRI VERIFIED
			4,  // MANIFEST OUTGOING
			5,  // OUTGOING SMU
			// 6,  // TRANSIT
			7,  // INCOMING
			8,  // DELIVERY
			9,  // POD
			10, // SHIPMENT LOST
			11, // SHIPMENT DAMAGE
			12, // OUTGOING RETURN
			13, // INCOMING RETURN
			14, // DELIVERY RETURN
			15, // SHIPMENT RETURN TO CLIENT
			20, // VOID
			21, // ENTRI (SEDANG DI PICKUP)
			22, // PICKED UP
			23, // ENTRI (PENDING PICKUP)
			24, // ENTRI (SEDANG PICKUP ULANG)
			25  // VOID PICKUP
		);

		return $list_rowstate_show;
	}
}

if (!function_exists('round_kg_cash')) {
	function round_kg_cash($n)
	{
		if ($n < 1) {
			return 1; // 1kg
		}

		// 0.3 >= 0.3
		// $value_kilo = round($n);

		$floor_kilo = floor(round($n, 1));

		$decimal_kilo = $n - $floor_kilo;

		if ($decimal_kilo < '0.3') {
			$rounded_kg = $floor_kilo;
		} else if ($decimal_kilo >= '0.3' && $decimal_kilo <= '0.7') {
			$rounded_kg = $floor_kilo + 0.5;
		} else {
			$rounded_kg = $floor_kilo + 1;
		}

		return $rounded_kg;
	}
}

if (!function_exists('insert_log_db')) {
	function insert_log_db($data)
	{
		$ci = &get_instance();
		if (!is_array($data)) {
			return false;
		}

		return $ci->post(null, true);
	}
}
