<?php
namespace App\Http\Libraries;
/**
 * 
 */
class Konversi
{
	public static function KonDecRomawi($angka){
		$hsl = "";
		if ($angka < 1 || $angka > 5000) { 
        	// Statement di atas buat nentuin angka ngga boleh dibawah 1 atau di atas 5000
			$hsl = "Batas Angka 1 s/d 5000";
		} else {
			while ($angka >= 1000) {
	            // While itu termasuk kedalam statement perulangan
	            // Jadi misal variable angka lebih dari sama dengan 1000
	            // Kondisi ini akan di jalankan
				$hsl .= "M"; 
	            // jadi pas di jalanin , kondisi ini akan menambahkan M ke dalam
	            // Varible hsl
				$angka -= 1000;
	            // Lalu setelah itu varible angka di kurangi 1000 ,
	            // Kenapa di kurangi
	            // Karena statment ini mengambil 1000 untuk di konversi menjadi M
			}
		}


		if ($angka >= 500) {
	        // statement di atas akan bernilai true / benar
	        // Jika var angka lebih dari sama dengan 500
			if ($angka > 500) {
				if ($angka >= 900) {
					$hsl .= "CM";
					$angka -= 900;
				} else {
					$hsl .= "D";
					$angka-=500;
				}
			}
		}
		while ($angka>=100) {
			if ($angka>=400) {
				$hsl .= "CD";
				$angka -= 400;
			} else {
				$angka -= 100;
			}
		}
		if ($angka>=50) {
			if ($angka>=90) {
				$hsl .= "XC";
				$angka -= 90;
			} else {
				$hsl .= "L";
				$angka-=50;
			}
		}
		while ($angka >= 10) {
			if ($angka >= 40) {
				$hsl .= "XL";
				$angka -= 40;
			} else {
				$hsl .= "X";
				$angka -= 10;
			}
		}
		if ($angka >= 5) {
			if ($angka == 9) {
				$hsl .= "IX";
				$angka-=9;
			} else {
				$hsl .= "V";
				$angka -= 5;
			}
		}
		while ($angka >= 1) {
			if ($angka == 4) {
				$hsl .= "IV"; 
				$angka -= 4;
			} else {
				$hsl .= "I";
				$angka -= 1;
			}
		}

		return ($hsl);
	}

	public static function nama_rekening($rekening){
		$data = array(
			'Account' => $rekening,
		);
		$payload = json_encode($data);

					// API URL
		$url = 'https://apps.bankjatim.co.id/Api/prod/v/ErCheck';

					// Create a new cURL resource
		$ch = curl_init($url);

					// Attach encoded JSON string to the POST fields
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

					// Set the content type to application/json
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

					// Return response instead of outputting
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					// Execute the POST request
		$result = curl_exec($ch);

					// Close cURL resource
		curl_close($ch);
		$result = json_decode($result);
		return $result;
	}

	public static function nama_bulan($angka){
		if($angka=='01') {
			$bulan = 'Januari';
		}else if($angka=='02'){
			$bulan = 'Februari';
		}else if($angka=='03'){
			$bulan = 'Maret';
		}else if($angka=='04'){
			$bulan = 'April';
		}else if($angka=='05'){
			$bulan = 'Mei';
		}else if($angka=='06'){
			$bulan = 'Juni';
		}else if($angka=='07'){
			$bulan = 'Juli';
		}else if($angka=='08'){
			$bulan = 'Agustus';
		}else if($angka=='09'){
			$bulan = 'September';
		}else if($angka=='10'){
			$bulan = 'Oktober';
		}else if($angka=='11'){
			$bulan = 'November';
		}else if($angka=='12'){
			$bulan = 'Desember';
		}else{
			$bulan = '';
		}
		return $bulan;
	}
}