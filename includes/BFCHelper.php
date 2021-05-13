<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'bfi_ItemType' ) ) {

	class bfi_ItemType {
		const Accommodation = 0;
		const Service = 1;
		const Package = 2;
		const Rental = 3;
		const Place = 4;
		const Beach = 5;
		const Experience = 6;

		// etc. }
	}
}
if ( ! class_exists( 'bfi_InputType' ) ) {

	class bfi_InputType {
		const yesno = 0;
		const text = 1;
		const textarea = 2;
		const number = 3;
		const data = 4;
		const datahours = 5;
		const dropdown = 6;
		const dropdownmultiple = 7;

		// etc. }
	}
}


if ( ! class_exists( 'bfi_TagsScope' ) ) {
	class bfi_TagsScope {
		const Merchant = 2**0;
		const Onsellunit = 2**1;
		const Resource = 2**2;
		const ResourceGroup = 2**3;
		const Offert = 2**4;
		const Event = 2**5;
		const Poi = 2**7;
		// etc. }
	}
}
if ( ! class_exists( 'bfi_Meal' ) ) {
	class bfi_Meal {
		const Breakfast = 1;
		const Lunch = 2;
		const Dinner = 4;
		const AllInclusive = 8;
		const BreakfastLunch = 3;
		const BreakfastDinner = 5;
		const LunchDinner = 6;
		const BreakfastLunchDinner = 7;
		const BreakfastLunchDinnerAllInclusive = 15;

		// etc. }
	}
}
if ( ! class_exists( 'bfiAgeType' ) ) {

	class bfiAgeType {
		public static $Adult = 0;
		public static $Seniors = 1;
		public static $Reduced = 2;


		// etc. }
	}
}
if ( ! class_exists( 'bfiRoomType' ) ) {

	class bfiRoomType {
		public static $Bedroom = 0;
		public static $Livingroom = 1;
		public static $Sittingroom = 2;
		public static $Kitchen = 3;
		public static $Mezzanine = 4;
		public static $Livingarea = 5;
		public static $Lunchroom = 6;
		public static $Cellar = 7;
		public static $Terrace = 8;


		// etc. }
	}
}

if ( ! class_exists( 'BFCHelper' ) ) {

	class BFCHelper {
		public static $defaultFallbackCode = 'en-gb';
		private static $sessionSeachParamKey = 'searchparams';
		private static $image_basePath = COM_BOOKINGFORCONNECTOR_BASEIMGURL;
		private static $image_basePathCDN = COM_BOOKINGFORCONNECTOR_IMGURL_CDN;
		private static $searchResults = array();
		private static $currentState = array();
		private static $defaultCheckMode = 5;
		private static $favouriteCookieName = "BFFavourites";
		private static $ordersCookieName = "BFOrders";

		private static $TwoFactorCookieName = "2faHSTDenabledWP";
		private static $UserDeviceIdName = "UserDeviceIdWP";
		private static $TwoFactorAuthenticationDeviceExpiration = 30;
		private static $TwoFactorPrefixClaimName = "TwoFactor.DeviceCode.";

		public static $listLanguages = array(
			'en' => 'English',
			'it' => 'italiano',
			'de' => 'Deutsch',
			'fr' => 'français',
			'es' => 'español',
			'da' => 'dansk',
			'aa' => 'Qafar',
			'af' => 'Afrikaans',
			'agq' => 'Aghem',
			'ak' => 'Akan',
			'am' => 'አማርኛ',
			'ar' => 'العربية',
			'arn' => 'Mapudungun',
			'as' => 'অসমীয়া',
			'asa' => 'Kipare',
			'ast' => 'asturianu',
			'az' => 'azərbaycan',
			'ba' => 'Башҡорт',
			'bas' => 'Ɓàsàa',
			'be' => 'Беларуская',
			'bem' => 'Ichibemba',
			'bez' => 'Hibena',
			'bg' => 'български',
			'bin' => 'Ẹ̀dó',
			'bm' => 'bamanakan',
			'bn' => 'বাংলা',
			'bo' => 'བོད་ཡིག',
			'br' => 'brezhoneg',
			'brx' => 'बड़ो',
			'bs' => 'bosanski',
			'byn' => 'ብሊን',
			'ca' => 'català',
			'ccp' => '𑄌𑄋𑄴𑄟𑄳𑄦',
			'ce' => 'нохчийн',
			'ceb' => 'Cebuano',
			'cgg' => 'Rukiga',
			'chr' => 'ᏣᎳᎩ',
			'co' => 'Corsu',
			'cs' => 'čeština',
			'cu' => 'церковнослове́нскїй',
			'cy' => 'Cymraeg',
			'dav' => 'Kitaita',
			'dje' => 'Zarmaciine',
			'dsb' => 'dolnoserbšćina',
			'dua' => 'duálá',
			'dv' => 'ދިވެހިބަސް',
			'dyo' => 'joola',
			'dz' => 'རྫོང་ཁ',
			'ebu' => 'Kĩembu',
			'ee' => 'Eʋegbe',
			'el' => 'Ελληνικά',
			'eo' => 'esperanto',
			'et' => 'eesti',
			'eu' => 'euskara',
			'ewo' => 'ewondo',
			'fa' => 'فارسی',
			'ff' => 'Pulaar',
			'fi' => 'suomi',
			'fil' => 'Filipino',
			'fo' => 'føroyskt',
			'fur' => 'furlan',
			'fy' => 'Frysk',
			'ga' => 'Gaeilge',
			'gd' => 'Gàidhlig',
			'gl' => 'galego',
			'gn' => 'Avañe’ẽ',
			'gsw' => 'Schwiizertüütsch',
			'gu' => 'ગુજરાતી',
			'guz' => 'Ekegusii',
			'gv' => 'Gaelg',
			'ha' => 'Hausa',
			'haw' => 'ʻŌlelo Hawaiʻi',
			'he' => 'עברית',
			'hi' => 'हिन्दी',
			'hr' => 'hrvatski',
			'hsb' => 'hornjoserbšćina',
			'hu' => 'magyar',
			'hy' => 'Հայերեն',
			'ia' => 'interlingua',
			'ibb' => 'Ibibio-Efik',
			'id' => 'Indonesia',
			'ig' => 'Asụsụ Igbo',
			'ii' => 'ꆈꌠꁱꂷ',
			'is' => 'íslenska',
			'iu' => 'Inuktitut',
			'ja' => '日本語',
			'jgo' => 'Ndaꞌa',
			'jmc' => 'Kimachame',
			'jv' => 'Basa Jawa',
			'ka' => 'ქართული',
			'kab' => 'Taqbaylit',
			'kam' => 'Kikamba',
			'kde' => 'Chimakonde',
			'kea' => 'kabuverdianu',
			'khq' => 'Koyra ciini',
			'ki' => 'Gikuyu',
			'kk' => 'қазақ тілі',
			'kkj' => 'kakɔ',
			'kl' => 'kalaallisut',
			'kln' => 'Kalenjin',
			'km' => 'ខ្មែរ',
			'kn' => 'ಕನ್ನಡ',
			'ko' => '한국어',
			'kok' => 'कोंकणी',
			'kr' => 'Kanuri',
			'ks' => 'کٲشُر',
			'ksb' => 'Kishambaa',
			'ksf' => 'rikpa',
			'ksh' => 'Kölsch',
			'ku' => 'کوردیی ناوەڕاست',
			'kw' => 'kernewek',
			'ky' => 'кыргызча',
			'la' => 'lingua latīna',
			'lag' => 'Kɨlaangi',
			'lb' => 'Lëtzebuergesch',
			'lg' => 'Luganda',
			'lkt' => 'Lakȟólʼiyapi',
			'ln' => 'lingála',
			'lo' => 'ລາວ',
			'lrc' => 'لۊری شومالی',
			'lt' => 'lietuvių',
			'lu' => 'Tshiluba',
			'luo' => 'Dholuo',
			'luy' => 'Luluhia',
			'lv' => 'latviešu',
			'mas' => 'Maa',
			'mer' => 'Kĩmĩrũ',
			'mfe' => 'kreol morisien',
			'mg' => 'Malagasy',
			'mgh' => 'Makua',
			'mgo' => 'metaʼ',
			'mi' => 'te reo Māori',
			'mk' => 'македонски',
			'ml' => 'മലയാളം',
			'mn' => 'монгол',
			'mni' => 'মৈতৈলোন্',
			'moh' => 'Kanien’kéha',
			'mr' => 'मराठी',
			'ms' => 'Melayu',
			'mt' => 'Malti',
			'mua' => 'MUNDAŊ',
			'my' => 'ဗမာ',
			'mzn' => 'مازرونی',
			'naq' => 'Khoekhoegowab',
			'nb' => 'norsk bokmål',
			'nd' => 'isiNdebele',
			'nds' => 'Neddersass’sch',
			'ne' => 'नेपाली',
			'nl' => 'Nederlands',
			'nmg' => 'Kwasio',
			'nn' => 'nynorsk',
			'nnh' => 'Shwóŋò ngiembɔɔn',
			'no' => 'norsk',
			'nqo' => 'ߒߞߏ',
			'nr' => 'isiNdebele',
			'nso' => 'Sesotho sa Leboa',
			'nus' => 'Thok Nath',
			'nyn' => 'Runyankore',
			'oc' => 'Occitan',
			'om' => 'Oromoo',
			'or' => 'ଓଡ଼ିଆ',
			'os' => 'ирон',
			'pa' => 'ਪੰਜਾਬੀ',
			'pap' => 'Papiamentu',
			'pl' => 'polski',
			'prg' => 'prūsiskan',
			'prs' => 'درى',
			'ps' => 'پښتو',
			'pt' => 'português',
			'quc' => 'Kiche',
			'quz' => 'Runasimi',
			'rm' => 'rumantsch',
			'rn' => 'Ikirundi',
			'ro' => 'română',
			'rof' => 'Kihorombo',
			'ru' => 'русский',
			'rw' => 'Kinyarwanda',
			'rwk' => 'Kiruwa',
			'sa' => 'संस्कृत',
			'sah' => 'Саха',
			'saq' => 'Kisampur',
			'sbp' => 'Ishisangu',
			'sd' => 'سنڌي',
			'se' => 'davvisámegiella',
			'seh' => 'sena',
			'ses' => 'Koyraboro senni',
			'sg' => 'Sängö',
			'shi' => 'ⵜⴰⵛⵍⵃⵉⵜ',
			'si' => 'සිංහල',
			'sk' => 'slovenčina',
			'sl' => 'slovenščina',
			'sma' => 'åarjelsaemiengïele',
			'smj' => 'julevusámegiella',
			'smn' => 'anarâškielâ',
			'sms' => 'sää´mǩiõll',
			'sn' => 'chiShona',
			'so' => 'Soomaali',
			'sq' => 'shqip',
			'sr' => 'srpski',
			'ss' => 'Siswati',
			'ssy' => 'Saho',
			'st' => 'Sesotho',
			'sv' => 'svenska',
			'sw' => 'Kiswahili',
			'syr' => 'ܣܘܪܝܝܐ',
			'ta' => 'தமிழ்',
			'te' => 'తెలుగు',
			'teo' => 'Kiteso',
			'tg' => 'Тоҷикӣ',
			'th' => 'ไทย',
			'ti' => 'ትግርኛ',
			'tig' => 'ትግረ',
			'tk' => 'Türkmen dili',
			'tn' => 'Setswana',
			'to' => 'lea fakatonga',
			'tr' => 'Türkçe',
			'ts' => 'Xitsonga',
			'tt' => 'Татар',
			'twq' => 'Tasawaq senni',
			'tzm' => 'Tamaziɣt n laṭlaṣ',
			'ug' => 'ئۇيغۇرچە',
			'uk' => 'українська',
			'ur' => 'اُردو',
			'uz' => 'o‘zbek',
			'vai' => 'ꕙꔤ',
			've' => 'Tshivenḓa',
			'vi' => 'Tiếng Việt',
			'vo' => 'Volapük',
			'vun' => 'Kyivunjo',
			'wae' => 'Walser',
			'wal' => 'ወላይታቱ',
			'wo' => 'Wolof',
			'xh' => 'isiXhosa',
			'xog' => 'Olusoga',
			'yav' => 'nuasue',
			'yi' => 'ייִדיש',
			'yo' => 'Èdè Yorùbá',
			'zgh' => 'ⵜⴰⵎⴰⵣⵉⵖⵜ',
			'zh' => '中文',
			'zu' => 'isiZulu',
			);

		// utilizzata per stati..
		public static $listCountries = array(
				'AX' => 'Åland Islands',
				'AF' => 'Afghanistan',
				'AL' => 'Albania',
				'DZ' => 'Algeria',
				'AD' => 'Andorra',
				'AO' => 'Angola',
				'AI' => 'Anguilla',
				'AQ' => 'Antarctica',
				'AG' => 'Antigua and Barbuda',
				'AR' => 'Argentina',
				'AM' => 'Armenia',
				'AW' => 'Aruba',
				'AU' => 'Australia',
				'AT' => 'Austria',
				'AZ' => 'Azerbaijan',
				'BS' => 'Bahamas',
				'BH' => 'Bahrain',
				'BD' => 'Bangladesh',
				'BB' => 'Barbados',
				'BY' => 'Belarus',
				'PW' => 'Belau',
				'BE' => 'Belgium',
				'BZ' => 'Belize',
				'BJ' => 'Benin',
				'BM' => 'Bermuda',
				'BT' => 'Bhutan',
				'BO' => 'Bolivia',
				'BQ' => 'Bonaire, Saint Eustatius and Saba',
				'BA' => 'Bosnia and Herzegovina',
				'BW' => 'Botswana',
				'BV' => 'Bouvet Island',
				'BR' => 'Brazil',
				'IO' => 'British Indian Ocean Territory',
				'VG' => 'British Virgin Islands',
				'BN' => 'Brunei',
				'BG' => 'Bulgaria',
				'BF' => 'Burkina Faso',
				'BI' => 'Burundi',
				'KH' => 'Cambodia',
				'CM' => 'Cameroon',
				'CA' => 'Canada',
				'CV' => 'Cape Verde',
				'KY' => 'Cayman Islands',
				'CF' => 'Central African Republic',
				'TD' => 'Chad',
				'CL' => 'Chile',
				'CN' => 'China',
				'CX' => 'Christmas Island',
				'CC' => 'Cocos (Keeling) Islands',
				'CO' => 'Colombia',
				'KM' => 'Comoros',
				'CG' => 'Congo (Brazzaville)',
				'CD' => 'Congo (Kinshasa)',
				'CK' => 'Cook Islands',
				'CR' => 'Costa Rica',
				'HR' => 'Croatia',
				'CU' => 'Cuba',
				'CW' => 'CuraÇao',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DK' => 'Denmark',
				'DJ' => 'Djibouti',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'EC' => 'Ecuador',
				'EG' => 'Egypt',
				'SV' => 'El Salvador',
				'GQ' => 'Equatorial Guinea',
				'ER' => 'Eritrea',
				'EE' => 'Estonia',
				'ET' => 'Ethiopia',
				'FK' => 'Falkland Islands',
				'FO' => 'Faroe Islands',
				'FJ' => 'Fiji',
				'FI' => 'Finland',
				'FR' => 'France',
				'GF' => 'French Guiana',
				'PF' => 'French Polynesia',
				'TF' => 'French Southern Territories',
				'GA' => 'Gabon',
				'GM' => 'Gambia',
				'GE' => 'Georgia',
				'DE' => 'Germany',
				'GH' => 'Ghana',
				'GI' => 'Gibraltar',
				'GR' => 'Greece',
				'GL' => 'Greenland',
				'GD' => 'Grenada',
				'GP' => 'Guadeloupe',
				'GT' => 'Guatemala',
				'GG' => 'Guernsey',
				'GN' => 'Guinea',
				'GW' => 'Guinea-Bissau',
				'GY' => 'Guyana',
				'HT' => 'Haiti',
				'HM' => 'Heard Island and McDonald Islands',
				'HN' => 'Honduras',
				'HK' => 'Hong Kong',
				'HU' => 'Hungary',
				'IS' => 'Iceland',
				'IN' => 'India',
				'ID' => 'Indonesia',
				'IR' => 'Iran',
				'IQ' => 'Iraq',
				'IM' => 'Isle of Man',
				'IL' => 'Israel',
				'IT' => 'Italia',
				'CI' => 'Ivory Coast',
				'JM' => 'Jamaica',
				'JP' => 'Japan',
				'JE' => 'Jersey',
				'JO' => 'Jordan',
				'KZ' => 'Kazakhstan',
				'KE' => 'Kenya',
				'KI' => 'Kiribati',
				'KW' => 'Kuwait',
				'KG' => 'Kyrgyzstan',
				'LA' => 'Laos',
				'LV' => 'Latvia',
				'LB' => 'Lebanon',
				'LS' => 'Lesotho',
				'LR' => 'Liberia',
				'LY' => 'Libya',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'MO' => 'Macao S.A.R., China',
				'MK' => 'Macedonia',
				'MG' => 'Madagascar',
				'MW' => 'Malawi',
				'MY' => 'Malaysia',
				'MV' => 'Maldives',
				'ML' => 'Mali',
				'MT' => 'Malta',
				'MH' => 'Marshall Islands',
				'MQ' => 'Martinique',
				'MR' => 'Mauritania',
				'MU' => 'Mauritius',
				'YT' => 'Mayotte',
				'MX' => 'Mexico',
				'FM' => 'Micronesia',
				'MD' => 'Moldova',
				'MC' => 'Monaco',
				'MN' => 'Mongolia',
				'ME' => 'Montenegro',
				'MS' => 'Montserrat',
				'MA' => 'Morocco',
				'MZ' => 'Mozambique',
				'MM' => 'Myanmar',
				'NA' => 'Namibia',
				'NR' => 'Nauru',
				'NP' => 'Nepal',
				'NL' => 'Netherlands',
				'AN' => 'Netherlands Antilles',
				'NC' => 'New Caledonia',
				'NZ' => 'New Zealand',
				'NI' => 'Nicaragua',
				'NE' => 'Niger',
				'NG' => 'Nigeria',
				'NU' => 'Niue',
				'NF' => 'Norfolk Island',
				'KP' => 'North Korea',
				'NO' => 'Norway',
				'OM' => 'Oman',
				'PK' => 'Pakistan',
				'PS' => 'Palestinian Territory',
				'PA' => 'Panama',
				'PG' => 'Papua New Guinea',
				'PY' => 'Paraguay',
				'PE' => 'Peru',
				'PH' => 'Philippines',
				'PN' => 'Pitcairn',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'QA' => 'Qatar',
				'IE' => 'Republic of Ireland',
				'RE' => 'Reunion',
				'RO' => 'Romania',
				'RU' => 'Russia',
				'RW' => 'Rwanda',
				'ST' => 'São Tomé and Príncipe',
				'BL' => 'Saint Barthélemy',
				'SH' => 'Saint Helena',
				'KN' => 'Saint Kitts and Nevis',
				'LC' => 'Saint Lucia',
				'SX' => 'Saint Martin (Dutch part)',
				'MF' => 'Saint Martin (French part)',
				'PM' => 'Saint Pierre and Miquelon',
				'VC' => 'Saint Vincent and the Grenadines',
				'SM' => 'San Marino',
				'SA' => 'Saudi Arabia',
				'SN' => 'Senegal',
				'RS' => 'Serbia',
				'SC' => 'Seychelles',
				'SL' => 'Sierra Leone',
				'SG' => 'Singapore',
				'SK' => 'Slovakia',
				'SI' => 'Slovenia',
				'SB' => 'Solomon Islands',
				'SO' => 'Somalia',
				'ZA' => 'South Africa',
				'GS' => 'South Georgia/Sandwich Islands',
				'KR' => 'South Korea',
				'SS' => 'South Sudan',
				'ES' => 'Spain',
				'LK' => 'Sri Lanka',
				'SD' => 'Sudan',
				'SR' => 'Suriname',
				'SJ' => 'Svalbard and Jan Mayen',
				'SZ' => 'Swaziland',
				'SE' => 'Sweden',
				'CH' => 'Switzerland',
				'SY' => 'Syria',
				'TW' => 'Taiwan',
				'TJ' => 'Tajikistan',
				'TZ' => 'Tanzania',
				'TH' => 'Thailand',
				'TL' => 'Timor-Leste',
				'TG' => 'Togo',
				'TK' => 'Tokelau',
				'TO' => 'Tonga',
				'TT' => 'Trinidad and Tobago',
				'TN' => 'Tunisia',
				'TR' => 'Turkey',
				'TM' => 'Turkmenistan',
				'TC' => 'Turks and Caicos Islands',
				'TV' => 'Tuvalu',
				'UG' => 'Uganda',
				'UA' => 'Ukraine',
				'AE' => 'United Arab Emirates',
				'GB' => 'United Kingdom (UK)',
				'US' => 'United States (US)',
				'UY' => 'Uruguay',
				'UZ' => 'Uzbekistan',
				'VU' => 'Vanuatu',
				'VA' => 'Vatican',
				'VE' => 'Venezuela',
				'VN' => 'Vietnam',
				'WF' => 'Wallis and Futuna',
				'EH' => 'Western Sahara',
				'WS' => 'Western Samoa',
				'YE' => 'Yemen',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe',
			);

		// utilizzata per Ricerca testuale..
		public static $listResultClasses = array(
			0 => 'zona globo',
			1 => 'stato',
			2 => 'zona stato',
			3 => 'regione',
			4 => 'zona regione',
			5 => 'città',
			6 => 'zona città',
			7 => 'poi',
			8 => 'categoria evento',
			9 => 'categoria merchant',
			10 => 'categoria gruppo di risorsa',
			11 => 'categoria risorsa',
			12 => 'tag evento',
			13 => 'tag merchant',
			14 => 'tag gruppo di risorsa',
			15 => 'tag risorsa',
			16 => 'evento',
			17 => 'merchant',
			18 => 'gruppo di risorsa',
			19 => 'risorsa',
		);

		public static $currencyCode = array(
			978 => 'EUR',
			191 => 'HRK',
			840 => 'USD',
			392 => 'JPY',
			124 => 'CAD',
			36 => 'AUD',
			643 => 'RUB',
			200 => 'CZK',
			702 => 'SGD',
			826 => 'GBP',
		);
		public static $listNameAnalytics = array(
			0 => 'Direct access',
			1 => 'Merchants Group List',
			2 => 'Resources Group List',
			3 => 'Resources Search List',
			4 => 'Merchants List',
			5 => 'Resources List',
			6 => 'Offers List',
			7 => 'Sales Resources List',
			8 => 'Sales Resources Search List',
			9 => 'Search Group List',
			11 => 'Event List',
			12 => 'Poi List',
			13 => 'Cart List',
		);
		private static $image_paths = array(
			'merchant' => '/merchants/',
			'resources' => '/products/unita/',
			'offers' => '/packages/',
			'services' => '/servizi/',
			'merchantgroup' => '/merchantgroups/',
			'tag' => '/tags/',
			'onsellunits' => '/products/unitavendita/',
			'resourcegroup' => '/products/condominio/',
			'variationplans' => '/variationplans/',
			'prices' => '/prices/',
			'events' => '/events/',
			'eventbanners' => '/eventbanners/',
			'poi' => '/poi/',
			'rating' => '/merchantcategories/',
			'mapsell' => '/mapsell/',


		);

		private static $image_path_resized = array(
			'merchant_list'						=> '148x148',
			'merchant_list_default'				=> '148x148',
			'resource_list_default'				=> '148x148',
			'onsellunit_list_default'			=> '148x148',
			'resource_list_default_logo'		=> '148x148',
			'resource_list_merchant_logo'		=> '200x70',
			'merchant_logo'						=> '200x70',
			'merchant_logo_small'				=> '65x65',
			'merchant_logo_small_top'			=> '250x90',
			'merchant_logo_small_rapidview'		=> '200x70',
			'resourcegroup_list_default'		=> '148x148',
			'offer_list_default'				=> '148x148',
			'resource_service'					=> '24x24',
			'resource_planimetry'				=> '400x250',
			'merchant_gallery_full'				=> '500x375',
			'merchant_mono_full'				=> '770x545',
			'merchant_gallery_thumb'			=> '85x85',
			'resource_gallery_full'				=> '692x450',
			'resource_mono_full'				=> '640x450',
			'resource_gallery_thumb'			=> '85x85',
			'resource_gallery_full_rapidview'	=> '416x290',
			'resource_gallery_thumb_rapidview'	=> '80x53',
			'resource_mono_full_rapidview'		=> '416x290',
			'resource_gallery_default_logo'		=> '100x100',
			'onsellunit_gallery_full'			=> '550x300',
			'onsellunit_mono_full'				=> '550x300',
			'onsellunit_default_logo'			=> '250x250',
			'onsellunit_gallery_thumb'			=> '85x85',
			'onsellunit_map_default'			=> '85x85',
			'onsellunit_showcase'				=> '180x180',
			'onsellunit_gallery'				=> '106x67',
			'resourcegroup_map_default'			=> '85x85',
			'merchant_merchantgroup'			=> '40x40',
			'resource_search_grid'			=> '380x215',
			'merchant_resource_grid' => '380x215',
			'small' => '201x113',
			'medium' => '380x215',
			'big' => '820x460',
			'logomedium' => '148x148',
			'logobig' => '170x95',
			'img40' => '40x40',
			'img24' => '24x24',
			'tag24' => '24x24',
			'banner' => '790x90'
		);

		private static $image_resizes = array(
			'merchant_list' => 'width=100&bgcolor=FFFFFF',
			'merchant_logo' => 'width=200&bgcolor=FFFFFF',
			'merchant_logo_small' => 'width=65&height=65&bgcolor=FFFFFF',
			'merchant_logo_small_top' => 'width=250&height=90&bgcolor=FFFFFF',
			'merchant_logo_small_rapidview' => 'width=180&height=65&bgcolor=FFFFFF',
			'resource_list_default' => 'width=148&height=148&mode=crop&anchor=middlecente&bgcolor=FFFFFF',
			'onsellunit_list_default' => 'width=148&height=148&mode=crop&anchor=middlecenter&bgcolor=FFFFFF',
			'resource_list_default_logo' => 'width=148&height=148&bgcolor=FFFFFF',
			'resource_list_merchant_logo' =>  'width=200&height=70&bgcolor=FFFFFF',
			'merchant_list_default' => 'width=148&height=148&bgcolor=FFFFFF',
			'resourcegroup_list_default' => 'width=148&height=148&bgcolor=FFFFFF',
			'offer_list_default' => 'width=148&height=148&bgcolor=FFFFFF',
			'resource_service' => 'width=24&height=24',
			'resource_planimetry' => 'width=400&height=250&mode=pad&anchor=middlecenter',
			'merchant_gallery_full' => 'width=500&height=375&mode=pad&anchor=middlecenter',
			'merchant_mono_full' => 'width=770&height=545&mode=crop&anchor=middlecenter&scale=both',
			'merchant_gallery_thumb' => 'width=85&height=85&mode=crop&anchor=middlecenter',
			'resource_gallery_full' => 'width=692&height=450&mode=pad&anchor=middlecenter&ext=.jpg',
			'resource_mono_full' => 'width=640&height=450&mode=pad&anchor=middlecenter&scale=both',
			'resource_gallery_thumb' => 'width=85&height=85&mode=crop&anchor=middlecenter',
			'resource_gallery_full_rapidview' => 'w=416&h=290&mode=crop&anchor=middlecenter&ext=.jpg',
			'resource_gallery_thumb_rapidview' => 'width=80&height=53&mode=crop&anchor=middlecenter',
			'resource_mono_full_rapidview' => 'w=416&h=290&mode=crop&anchor=middlecenter&ext=.jpg',
			'resource_gallery_default_logo' => 'w=100&h=100&mode=pad&anchor=middlecenter&ext=.jpg',
			'onsellunit_gallery_full' => 'w=550&h=300&bgcolor=EDEDED&mode=pad&anchor=middlecenter&ext=.jpg',
			'onsellunit_mono_full' => 'width=550&height=300&mode=crop&anchor=middlecenter&scale=both',
			'onsellunit_default_logo' => 'width=250&height=250&bgcolor=FFFFFF',
			'onsellunit_gallery_thumb' => 'width=85&height=85&mode=crop&anchor=middlecenter',
			'onsellunit_map_default' => 'width=85&height=85&bgcolor=FFFFFF',
			'onsellunit_showcase' => 'width=180&height=180&bgcolor=FFFFFF&mode=crop&anchor=middlecenter',
			'onsellunit_gallery' => 'width=106&height=67&bgcolor=FFFFFF',
			'resourcegroup_map_default' => 'width=85&height=85&bgcolor=FFFFFF',
			'merchant_merchantgroup' => 'width=40&height=40',
			'small' => 'width=201&height=113&mode=crop&anchor=middlecenter',
			'medium' => 'width=380&height=215&mode=crop&anchor=middlecenter',
			'big' => 'width=820&height=460&mode=crop&anchor=middlecenter',
			'logomedium' => 'width=148&height=148&anchor=middlecenter&bgcolor=FFFFFF',
			'logobig' => 'width=170&height=95&anchor=middlecenter&bgcolor=FFFFFF',
			'tag24' => 'width=24&height=24'
		);

		public static $daySpan = '+7 day';
		public static $defaultDaysSpan = '+7 days';
		public static $defaultDuration = 7;
		public static $defaultAdultsAge = COM_BOOKINGFORCONNECTOR_ADULTSAGE;
		public static $defaultChildrensAge = COM_BOOKINGFORCONNECTOR_CHILDRENSAGE;
		public static $defaultAdultsQt = COM_BOOKINGFORCONNECTOR_ADULTSQT;
		public static $defaultSenioresAge = COM_BOOKINGFORCONNECTOR_SENIORESAGE;
		public static $onsellunitDaysToBeNew = 120;

		//public static $typologiesMerchantResults = array(1,6);
		public static function isUnderHTTPS() {
			return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' );
		}

		public static function isMerchantAnonymous($id) {
			if (defined('COM_BOOKINGFORCONNECTOR_ANONYMOUS_TYPE')) {
				$aAnon = explode(",",COM_BOOKINGFORCONNECTOR_ANONYMOUS_TYPE);
				return in_array($id, $aAnon);
			}
			return false;
		}

		public static function showMerchantRatingByCategoryId($id) {
			if (defined('COM_BOOKINGFORCONNECTOR_MERCHANTCATEGORIES_SHOW_RATING_MERCHANT')) {
				$aAnon = explode(",",COM_BOOKINGFORCONNECTOR_MERCHANTCATEGORIES_SHOW_RATING_MERCHANT);
				return in_array($id, $aAnon);
			}
			return false;
		}

		public static function getCategoryMerchantResults($language='') {
			$groupOnSearch = array();
			$merchantCategories = BFCHelper::getMerchantCategories($language);
			if(!empty($merchantCategories)){
			$groupOnSearch = array_unique(array_map(function ($i) {
				if($i->GroupOnSearch){
					return $i->MerchantCategoryId;
				}
				return 0;
				}, $merchantCategories));
			}
			return $groupOnSearch;
		}

		public static function getTypologiesMerchantResults() {
			if (self::isMerchantBehaviour()) {
				return array();
			}
			return array(1,6);
		}
		public static function getAddressDataByMerchant($id) {
			if (defined('COM_BOOKINGFORCONNECTOR_MERCHANTCATEGORIES_RESOURCE_ADDRESSDATA_BY_MERCHANT')) {
				$aAnon = explode(",",COM_BOOKINGFORCONNECTOR_MERCHANTCATEGORIES_RESOURCE_ADDRESSDATA_BY_MERCHANT);
				return in_array($id, $aAnon);
			}
			return false;
		}

		public static function isMerchantBehaviour() {
			if (defined('COM_BOOKINGFORCONNECTOR_MERCHANTBEHAVIOUR')) {
				if (COM_BOOKINGFORCONNECTOR_MERCHANTBEHAVIOUR) {
					return true;
				}
			}
			return false;
		}

		public static function setSearchResult($searchid, $value) {
			if ($value == null) {
				if (array_key_exists($searchid, self::$searchResults)) {
					unset(self::$searchResults[$searchid]);
				}
			} else
			{
				self::$searchResults[$searchid] = $value;
			}
		}

		public static function getSearchResult($searchid) {
			if (array_key_exists($searchid, self::$searchResults)) {
				return self::$searchResults[$searchid];
			}
			return null;
		}

		public static function getItem($xml, $itemName, $itemContext = null) {
			if ($xml==null || $itemName == null) return '';
				$currErrLev = error_reporting();
				error_reporting(0);
			try {
				$xdoc = new SimpleXmlElement($xml);
				if (isset($itemContext)) $xdoc= $xdoc->$itemContext;
				$item = $xdoc->$itemName;

			} catch (Exception $e) {
				// maybe it's not a well formed XML?
				return $itemName;
			}
				error_reporting($currErrLev);
			return $item;
		}

		public static function priceFormat($number, $decimal=2,$sep1=',',$sep2='.') {
			if(empty($number)){
				$number =0;
			}
			//conversion valuta;
			$defaultcurrency = bfi_get_defaultCurrency();
			$currentcurrency = bfi_get_currentCurrency();

			if($defaultcurrency!=$currentcurrency){
				//try to convert
				$currencyExchanges = BFCHelper::getCurrencyExchanges();
				if (isset($currencyExchanges[$currentcurrency]) ) {
					$number = $number*$currencyExchanges[$currentcurrency];
				}
			}
			return number_format($number, $decimal, $sep1, $sep2);
		}

	/* -------------------------------- */

		public static function getCartMultimerchantEnabled() {
	//		$model = new BookingForConnectorModelPortal;
	//		return $model->getCartMultimerchantEnabled();
			return true;
		}
		public static function GetPrivacy($language) {
			$model = new BookingForConnectorModelPortal;
			return $model->getPrivacy($language);
		}

		public static function getCurrencyExchanges() {
			$model = new BookingForConnectorModelPortal;
			return $model->getCurrencyExchanges();
		}

		public static function getDefaultCurrency() {
			$model = new BookingForConnectorModelPortal;
			return $model->getDefaultCurrency();
		}

		public static function getSubscriptionInfos() {
			$model = new BookingForConnectorModelPortal;
			return $model->getSubscriptionInfos();
		}

		public static function GetAdditionalPurpose($language) {
			$model = new BookingForConnectorModelPortal;
			return $model->getAdditionalPurpose($language);
		}
		public static function GetPhoneByMerchantId($merchantId,$language) {
			$model = new BookingForConnectorModelMerchantDetails;
			return $model->getPhoneByMerchantId($merchantId,$language);
		}

		public static function GetProductCategoryForSearch($language='', $typeId = 1,$merchantid=0) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPortal;
			return $model->getProductCategoryForSearch($language, $typeId,$merchantid);
		}

	//	public static function GetFaxByMerchantId($merchantId,$language) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('MerchantDetails', 'BookingForConnectorModel');
	//		return $model->GetFaxByMerchantId($merchantId,$language);
	//	}

	//	public static function setCounterByMerchantId($merchantId = null, $what='', $language='') {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('MerchantDetails', 'BookingForConnectorModel');
	//		return $model->setCounterByMerchantId($merchantId, $what, $language);
	//	}
	//	public static function setCounterByResourceId($resourceId = null, $what='', $language='') {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('OnSellUnit', 'BookingForConnectorModel');
	//		return $model->setCounterByResourceId($resourceId, $what, $language);
	//	}

	/* -------------------------------- */
	//	public static function getMerchant() {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('MerchantDetails', 'BookingForConnectorModel');
	//		return $model->getItem();
	//	}
		public static function getMerchantFromServicebyId($merchantId) {
			$model = new BookingForConnectorModelMerchantDetails;
			return $model->getMerchantFromServicebyId($merchantId);
		}
		public static function getMerchantbyId($merchantId) {
			$model = new BookingForConnectorModelMerchantDetails;
			return $model->getMerchantFromServicebyId($merchantId);
		}
		public static function getMerchantOfferFromService($offerId, $language='') {
			$model = new BookingForConnectorModelMerchantDetails;
			return $model->getMerchantOfferFromService($offerId, $language);
		}

		public static function getResourcegroupFromServicebyId($resourceId) {
			$model = new BookingForConnectorModelResourcegroup;
			return $model->getResourcegroupFromService($resourceId);
		}
		public static function getMerchantRatings($start, $limit, $merchantId = null,$language='',$fromCache=0) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//			$model = JModelLegacy::getInstance('MerchantDetails', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelMerchantDetails;
			return $model->getMerchantRatingsFromService($start, $limit, $merchantId, null,$language,$fromCache);
		}

	//	public static function getRatingByMerchantId($merchantId) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('MerchantDetails', 'BookingForConnectorModel');
	//		return $model->getMerchantRatingAverageFromService($merchantId);
	//	}
	//	public static function getRatingsByOrderId($orderId) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Ratings', 'BookingForConnectorModel');
	//		return $model->getRatingsByOrderIdFromService($orderId);
	//	}
	//	public static function getTotalRatingsByOrderId($orderId) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Ratings', 'BookingForConnectorModel');
	//		return $model->getTotalRatingsByOrderId($orderId);
	//	}
		public static function getResourceRatingAverage($merchantId, $resourceId) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//			$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->getRatingAverageFromService($merchantId, $resourceId);


		}
		public static function getResourceRating($start = 0, $limit=5, $merchantId = null, $resourceId = null,$language='',$fromCache=0) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//			$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			//$model = new BookingForConnectorModelResource;
			//return $model->getRatingsFromService($start, $limit, $resourceId);

			$model = new BookingForConnectorModelMerchantDetails;
			$model->setMerchantId($merchantId);
			$model->setResourceId($resourceId);
			return $model->getMerchantRatingsFromService($start, $limit, $merchantId, $resourceId,$language,$fromCache);
		}

		public static function getMerchantGroupsByMerchantId($merchantId) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelMerchantDetails;
			return $model->getMerchantGroupsByMerchantIdFromService($merchantId);
		}

		public static function getGeographicZones() {
			$model = new BookingForConnectorModelMerchants;
			return $model->getGeographicZones();
		}

		public static function getMasterTypologies($onlyEnabled = true) {
			$model = new BookingForConnectorModelSearch;
			return $model->getMasterTypologies($onlyEnabled);
		}
		public static function GetAlternativeDates($checkin, $duration, $paxes, $paxages, $merchantId, $resourcegroupId, $resourceId, $cultureCode, $points, $userid, $tagids, $merchantsList, $availabilityTypes, $itemTypeIds, $domainLabel, $merchantCategoryIds = null, $masterTypeIds = null, $merchantTagsIds = null, $groupResultType = 0, $resourcesList = null) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//			$model = JModelLegacy::getInstance('Search', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelSearch;
			return $model->GetAlternativeDates($checkin, $duration, $paxes, $paxages, $merchantId, $resourcegroupId, $resourceId, $cultureCode, $points, $userid, $tagids, $merchantsList, $availabilityTypes, $itemTypeIds, $domainLabel, $merchantCategoryIds, $masterTypeIds, $merchantTagsIds, $groupResultType, $resourcesList);
		}

		public static function getMerchantGroups() {
			$model = new BookingForConnectorModelMerchants;
			return $model->getMerchantGroups();
		}

		public static function getEventsSearch($checkin, $checkout, $maxitems, $cultureCode, $userid = null, $domainLabel = null, $merchantId = null, $points = null) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//			$model = JModelLegacy::getInstance('Search', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelSearchEvent;
			$params = BFCHelper::getSearchEventParamsSession();

			if(empty($params) || !is_array($params )) {
			    $params = array();
			}
//			$params['checkin'] = BFCHelper::parseStringDateTime($checkin,'YmdHis');
//			$params['checkout'] = BFCHelper::parseStringDateTime($checkout,'YmdHis');
			$params['checkin'] = $checkin;
			$params['checkout'] = $checkout;
			BFCHelper::setSearchEventParamsSession($params);
			return $model->getSearchResults(0, $maxitems, '', '', false,false,$cultureCode, 0);
		}

		public static function getMerchantCategories($language='') {
		  $model = new BookingForConnectorModelMerchants;
			return $model->getMerchantCategories($language);
		}
		public static function getMerchantCategoriesForRequest($language='') {
		  $model = new BookingForConnectorModelMerchants;
			return $model->getMerchantCategoriesForRequest($language);
		}

		public static function getEventCategories($language='') {
		  $model = new BookingForConnectorModelEvents;
			return $model->getCategories($language);
		}

		public static function getEventById($id='', $language='') {
			$model = new BookingForConnectorModelEvent;
			return $model->getDetails($id, $language);
		}

		public static function getServicesByMerchantsCategoryId($merchantCategoryId,$language='') {
			$model = new BookingForConnectorModelMerchants;
			return $model->getServicesByMerchantsCategoryId($merchantCategoryId,$language);
		}

		public static function GetPolicy($resourcesId,$language='') {
			$model = new BookingForConnectorModelResource;
			return $model->GetPolicy($resourcesId,$language);
		}
		public static function GetPolicyById($policId, $language = '', $userId = null) {
			$model = new BookingForConnectorModelResource;
			return $model->GetPolicyById($policId, $language, $userId);
		}

		public static function GetCrossSellResourcesByIds($listsId,$language='',$start, $limit) {
		  $model = new BookingForConnectorModelResources;
			return $model->GetCrossSellResourcesByIds($listsId,$language,$start, $limit);
		}
		public static function GetResourcesByIds($listsId,$language='') {
		  $model = new BookingForConnectorModelResources;
			return $model->GetResourcesByIds($listsId,$language);
		}
		public static function GetResourcesById($id,$language='') {
		  $model = new BookingForConnectorModelResource;
			return $model->getItem($id);
		}
		public static function GetExperienceById($id,$language='') {
		  $model = new BookingForConnectorModelExperience;
			return $model->getItem($id);
		}
		public static function getResourcesbyIdMerchant($start, $limit, $merchantId = NULL, $parentId = NULL) {
		  $model = new BookingForConnectorModelMerchantDetails;
			return $model->getMerchantResourcesFromSearch($start, $limit, $merchantId, $parentId);
		}
//		public static function GetAlternateResources($start, $limit, $ordering = null, $direction = null, $merchantid = null,  $resourcegroupid = null, $ignorePagination = false, $jsonResult = false, $excludedResources = array(), $requiredOffers = array(), $overrideFilters = null, $language='') {
//	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
//			$model = new BookingForConnectorModelResource;
//			return $model->getSearchResults($start, $limit, $ordering, $direction, $merchantid, $resourcegroupid, $ignorePagination, false, $excludedResources, $requiredOffers, $overrideFilters, $language);
//		}


		public static function getDiscountDetails($discountId, $hasRateplans) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->getDiscountDetails($discountId,$hasRateplans);
		}

		public static function GetResourcesOnSellByIds($listsId,$language='') {
			$model = new BookingForConnectorModelOnSellUnits;
			return $model->GetResourcesByIds($listsId,$language);
		}
		public static function GetServicesByIds($listsId,$language='') {
			$model = new BookingForConnectorModelServices;
			return $model->getServicesByIds($listsId,$language);
		}

		public static function getServicesForSearchOnSell($language='') {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('OnSellUnits', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOnSellUnits;
			return $model->getServicesForSearchOnSell($language);
		}

		public static function getServicesForSearch($language='') {
			$model = new BookingForConnectorModelResources;
			return $model->getServicesForSearch($language);
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resources', 'BookingForConnectorModel');
	//		return $model->getServicesForSearch($language);
		}


		public static function getMerchantsByIds($listsId, $language = '') {
			$model = new BookingForConnectorModelMerchants;
			return $model->getMerchantsByIds($listsId, $language);
		}

		public static function getResourcegroupByIds($listsId,$language='') {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Condominiums', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResourcegroup;
			return $model->getResourcegroupByIds($listsId,$language);
		}

		public static function getMerchantsSearch($text,$start,$limit,$order,$direction) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Merchants', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelMerchants;
			return $model->getMerchantsForSearch($text,$start,$limit,$order,$direction);
		}

		public static function getTags($language='',$categoryIds='') {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Tag', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelTags;
			return $model->getTags($language,$categoryIds,null,null);
		}
		public static function GetTagsByIds($listsId,$language='',$viewContextType='') {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Tag', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelTags;
			return $model->GetTagsByIds($listsId,$language,$viewContextType);
		}

		public static function getMerchantsByTagIdsExt($tagids, $start = null, $limit = null) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Tag', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelTags;
			return $model->getMerchantsByTagIdsExt($tagids, $start, $limit);
		}
		public static function getEventsByTagIds($tagids, $start = null, $limit = null) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Tag', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelEvents;
			return $model->getEventsByTagIds($tagids, $start, $limit);
		}
		public static function getEventsByMerchantId($merchantId, $start = null, $limit = null) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Tag', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelEvents;
			return $model->getEventsByTagIds('', $start, $limit,$merchantId);
		}
		public static function getResourcesByTagIds($tagids, $start = null, $limit = null) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Tag', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelTags;
			return $model->getResourcesByTagIds($tagids, $start, $limit);
		}

		public static function getPointsofinterestsByTagIds($tagids, $start = null, $limit = null) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Tag', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPointsofinterests;
			return $model->getPointsofinterestsByTagIds($tagids, $start, $limit);
		}
		public static function prepareOrderData($formData, $customerData=null, $suggestedStay=null, $otherData=null, $creditCardData=null) {
			if ($formData == null) {
				$formData = $_POST['form'];
			}

			$userNotes = $formData['note'];
			$cultureCode = $formData['cultureCode'];
			$merchantId = $formData['merchantId'];
			$orderType = $formData['orderType'];
			$label = $formData['label'];
			$customerDatas = array($customerData);
			$bt = array();
			if(!empty($formData['bookingType']) &&  strpos($formData['bookingType'].'',':') !== false ){
				$bt = explode(':',$formData['bookingType'].'');
			}
			if(!isset($suggestedStay)){
				$suggestedStay = new stdClass;
			}
			array_push($bt, null,null);

			if(isset($bt[0])){
				$suggestedStay->MerchantBookingTypeId = $bt[0];
			}

			$orderData = array(
					'customerData' => $customerDatas,
					'suggestedStay' =>$suggestedStay,
					'creditCardData' => $creditCardData,
					'otherNoteData' => $otherData,
					'merchantId' => $merchantId,
					'orderType' => $orderType,
					'userNotes' => $userNotes,
					'label' => $label,
					'cultureCode' => $cultureCode
					);

			return $orderData;
		}

		public static function getLastOrderPayment($orderId = 0) {
			$model = new BookingForConnectorModelPayment;
			return $model->GetLastOrderPayment($orderId);
		}

		public static function getSingleOrderFromService($orderId = 0) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Orders', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOrders;
			return $model->getSingleOrderFromService($orderId);
		}

		public static function getMerchantPaymentData($bookingTypeId) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Payment', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPayment;
			return $model->getMerchantPaymentData($bookingTypeId);
		}

		public static function GetOrderDetailsById($orderId,$culturecode='') {
			$model = new BookingForConnectorModelOrders;
			return $model->GetOrderDetailsById($orderId,$culturecode);
		}
		public static function setOrder($customerData = NULL, $suggestedStay = NULL, $creditCardData = NULL, $otherNoteData = NULL, $merchantId = NULL, $orderType = NULL, $userNotes = NULL, $label = NULL, $cultureCode = NULL, $processOrder = NULL, $priceType, $merchantBookingTypeId = NULL, $policyId = NULL, $crewData = NULL) {
			$model = new BookingForConnectorModelOrders;
			return $model->setOrder($customerData, $suggestedStay, $creditCardData, $otherNoteData, $merchantId, $orderType, $userNotes, $label, $cultureCode, $processOrder, $priceType,$merchantBookingTypeId, $policyId,$crewData);
		}

		public static function setOrderStatus($orderId = NULL, $status = NULL, $sendEmails = false, $setAvailability = false, $paymentData = NULL)  {
			$model = new BookingForConnectorModelOrders;
			return $model->setOrderStatus($orderId, $status, $sendEmails, $setAvailability, $paymentData);
		}

		public static function updateCCdata($orderId, $creditCardData = NULL, $processOrder = NULL) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Orders', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOrders;
			return $model->updateCCdata($orderId, $creditCardData, $processOrder);
		}

		public static function setOrderPayment($orderId = NULL, $status = 0, $sendEmails = false,$amount = 0,$bankId, $paymentData = NULL, $cultureCode = NULL, $processOrder = NULL)  {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Payment', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPayment;
			return $model->setOrderPayment($orderId, $status, $sendEmails, $amount,$bankId, $paymentData, $cultureCode, $processOrder);
		}
		public static function setInfoRequest($customerData = NULL, $suggestedStay = NULL, $otherNoteData = NULL, $merchantId = NULL, $type = NULL, $userNotes = NULL, $label = NULL, $cultureCode = NULL, $processInfoRequest = NULL) {
			$model = new BookingForConnectorModelInfoRequests;
			return $model->setInfoRequest($customerData, $suggestedStay, $otherNoteData, $merchantId, $type, $userNotes, $label, $cultureCode, $processInfoRequest);
		}

		public static function getCountAllResourcesOnSell() {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('OnSellUnits', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOnSellUnits;
			return $model->getAllResources();
		}

		public static function getStartDateByMerchantId($merchantId) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->getStartDateByMerchantId($merchantId);
		}

		public static function getEndDateByMerchantId($merchantId) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->getEndDateByMerchantId($merchantId);
		}

		public static function getCheckInDates($resourceId = null,$ci = null) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->getCheckInDatesFromService($resourceId ,$ci);
		}
		public static function GetCheckInDatesPerTimes($resourceId = null,$ci = null, $limitTotDays = 0) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->GetCheckInDatesPerTimes($resourceId ,$ci, $limitTotDays);
		}
		public static function GetListCheckInDayPerTimes($resourceId = null,$ci = null, $limitTotDays = 0) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->GetListCheckInDayPerTimes($resourceId , $ci, $limitTotDays);
		}

		public static function GetCheckInDatesTimeSlot($resourceId = null,$ci = null) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->GetCheckInDatesTimeSlot($resourceId ,$ci);
		}


		public static function getCheckOutDates($resourceId = null,$checkIn = null) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->getCheckOutDatesFromService($resourceId ,$checkIn);
		}
		public static function GetCheckOutDatesPerTimes($resourceId = null,$checkIn = null) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->getCheckOutDatesPerTimesFromService($resourceId ,$checkIn);
		}

		public static function GetMostRestrictivePolicyByIds($policyIds, $cultureCode, $stayConfiguration ='', $priceValue=null, $days=null) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//			$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->GetMostRestrictivePolicyByIds($policyIds, $cultureCode, $stayConfiguration, $priceValue, $days);
		}

		public static function GetPolicyByIds($policyIds, $cultureCode, $userId = null) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//			$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->GetPolicyByIds($policyIds, $cultureCode, $userId);
		}

		public static function getCompleteRateplansStayFromParameter($resourceId = null,$checkIn = null,$duration = 1,$paxages = '',$selectablePrices='',$packages,$pricetype='',$rateplanId=null,$variationPlanId=null ) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Resource', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelResource;
			return $model->getCompleteRateplansStayFromParameter($resourceId,$checkIn,$duration,$paxages,$selectablePrices,$packages,$pricetype,$rateplanId,$variationPlanId);
		}

		public static function GetCompleteRatePlansStayWP($resourceId = null,$checkIn = null,$duration = 1,$paxages = '',$selectablePrices='',$packages,$pricetype='',$rateplanId=null,$variationPlanId=null,$language="",$merchantBookingTypeId = "", $getAllResults=false,$resourceItemId=null ,$timeSlotId=null, $getAllPaxConfigurations = null) {
			$model = new BookingForConnectorModelResource;
			return $model->GetCompleteRatePlansStayWP($resourceId,$checkIn,$duration,$paxages,$selectablePrices,$packages,$pricetype,$rateplanId,$variationPlanId,$language,$merchantBookingTypeId, $getAllResults,$resourceItemId ,$timeSlotId,$getAllPaxConfigurations);
		}

		public static function GetRelatedResourceStays($merchantId,$relatedProductid,$excludedIds,$checkin,$duration,$paxages,$variationPlanId,$language="",$resourcegroupId=0,$checkout=null,$checkFullPeriod=false,$itemTypeIds ){
			$model = new BookingForConnectorModelResource;
			return $model->GetRelatedResourceStays($merchantId,$relatedProductid,$excludedIds,$checkin,$duration,$paxages,$variationPlanId,$language,$resourcegroupId,$checkout,$checkFullPeriod,$itemTypeIds );
		}

		public static function getSearchPackages($merchantId,$relatedProductid,$excludedIds,$checkin,$duration,$paxages,$variationPlanId,$language="",$resourcegroupId=0,$availabilityTypes ='',$itemTypeIds='',$minqt=1 ,$maxqt=1){
			$model = new BookingForConnectorModelResource;
			return $model->getSearchPackages($merchantId,$relatedProductid,$excludedIds,$checkin,$duration,$paxages,$variationPlanId,$language,$resourcegroupId,$availabilityTypes,$itemTypeIds,$minqt,$maxqt );
		}

		public static function GetMapAvailabilitiesByProductGroupId($productGroupId,$checkIn,$checkOut,$paxes,$paxages,$sectorId, $cultureCode = NULL){
			$cultureCode = !empty($cultureCode) ? $cultureCode: $GLOBALS['bfi_lang'];
//			$currParam = array();
//			$currParam['productGroupId'] = !empty($productGroupId) ? $productGroupId: 0;
//			$currParam['checkIn'] = !empty($checkIn) ? $checkIn: "";
//			$currParam['checkOut'] = !empty($checkOut) ? $checkOut: "";
//			$currParam['paxes'] = !empty($paxes) ? $paxes: "";
//			$currParam['paxages'] = !empty($paxages) ? $paxages: "";
//			$currParam['sectorId'] = !empty($sectorId) ? $sectorId: "";
//			$currParam['cultureCode'] = $cultureCode;

			$model = new BookingForConnectorModelSearchMapSells;
//			$model->setParam($currParam);
			return $model->GetMapAvailabilitiesByProductGroupId($productGroupId,$checkIn,$checkOut,$paxes,$paxages,$sectorId, $cultureCode);
		}

		public static function setRating(
				$name = NULL,
				$city = NULL,
				$typologyid = NULL,
				$email = NULL,
				$nation = NULL,
				$merchantId = NULL,
				$value1= NULL,
				$value2= NULL,
				$value3= NULL,
				$value4= NULL,
				$value5= NULL,
				$totale = NULL,
				$pregi =NULL,
				$difetti =NULL,
				$userId = NULL,
				$cultureCode = NULL,
				$checkin= NULL,
				$resourceId= NULL,
				$orderId= NULL,
				$label = NULL,
				$otherData = NULL
			) {

			$model = new BookingForConnectorModelRatings;
			return $model->setRating($name, $city, $typologyid, $email, $nation, $merchantId,$value1, $value2, $value3, $value4, $value5, $totale, $pregi, $difetti, $userId, $cultureCode,$checkin, $resourceId, $orderId, $label, $otherData);
		}


		public static function getCriteoConfiguration($pagetype = 0, $merchantsList = array(), $orderId = null) {
	//		JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//		$model = JModelLegacy::getInstance('Criteo', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelCriteo;
			return $model->getCriteoConfiguration($pagetype, $merchantsList,$orderId);
		}


		public static function getSlug($string) {
			$s = array();
			$r = array();
			$s[0] = "/\&/";
			$r[0] = "and";
			$s[1] = '/[^a-z0-9-]/';
			$r[1] = '-';
			$s[2] = '/-+/';
			$r[2] = '-';
			$string = preg_replace( $s, $r, strtolower( trim( $string ) ) );
			return $string;
		}

		public static function containsUrl($string) {
			$re = '/^[a-zA-Z0-9\-\.\:\\\\]+\.(com|org|net|mil|edu|COM|ORG|NET|MIL|EDU)$/';
			$re1 = '/(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/';
			$trimmed = trim( $string );
			if( preg_match($re,$trimmed) || preg_match($re1,$trimmed)){
				return true;
			}
			return false;

		}

		public static function getLanguage($xml, $langCode, $fallbackCode = 'en-gb', $opts = array() ) {
			if (!isset($xml)) {
				return '';
			}
			$retVal = $xml;
			if (strpos($xml,'<languages>') !== false) {
				if ($fallbackCode == null || !isset($fallbackCode)) {
					$fallbackCode = self::$defaultFallbackCode;
				}
				$langCode = strtolower($langCode);
				$fallbackCode = strtolower($fallbackCode);
				if (strlen ($langCode) > 2) {
					$langCode = substr($langCode,0,2);
				}
				if (strlen ($fallbackCode) > 2) {
					$fallbackCode = substr($fallbackCode,0,2);
				}
				$xml = self::stripInvalidXml($xml);
				$xdoc = new SimpleXmlElement($xml);
				$item = $xdoc->xpath("language [@code='" . $langCode . "']");
				$result = '';
				$retVal = '';
				if(!empty($item)){
					$result = (string)$item[0];
				}
				if (($result == '') && $fallbackCode != '') {
					$item = $xdoc->xpath("language [@code='" . $fallbackCode . "']");
				}
				if(!empty($item)){
					$retVal = (string)$item[0];
				}
				//$retVal = (string)$item[0];
			}

			if (isset($opts) && count($opts) > 0) {
				foreach ($opts as $key => $opt) {
					switch (strtolower($key)) {
						case 'ln2br':
							$retVal = nl2br($retVal, true);
							break;
						case 'htmlencode':
							$retVal = htmlentities($retVal, ENT_COMPAT);
							break;
						case 'striptags':
							$retVal = strip_tags($retVal, "<br><br/>");
							break;
						case 'nomore1br':
							$retVal = preg_replace("/\n+/", "\n", $retVal);
							break;
						case 'nobr':
							$retVal = preg_replace("/\n+/", " ", $retVal);
							break;
						case 'bbcode':
							$search = array (
								'~\[b\](.*?)\[/b\]~s',
								'~\[i\](.*?)\[/i\]~s',
								'~\[u\](.*?)\[/u\]~s',
								'~\[s\](.*?)\[/s\]~s',
								'~\[ul\](.*?)\[/ul\]~s',
								'~\[li\](.*?)\[/li\]~s',
								'~\[ol\](.*?)\[/ol\]~s',
								'~\[size=(.*?)\](.*?)\[/size\]~s',
								'~\[color=([^"><]*?)\](.*?)\[/color\]~s',
								'~\[url=(.*?)(\])(.*?)\[\/url\]~s',
								'~\[img\](https?://[^"><]*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s',
								'~\[img=(.*?)x(.*?)\](https?://[^"><]*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s',
								'~\[center\](.*?)\[/center\]~s',
								'~\[td\](.*?)\[/td\]~s',
								'~\[tr\](.*?)\[/tr\]~s',
								'~\[table\](.*?)\[/table\]~s',
								'~\[sup\](.*?)\[/sup\]~s',
								'~\[sub\](.*?)\[/sub\]~s',
								'~\[right\](.*?)\[/right\]~s',
								'~\[justify\](.*?)\[/justify\]~s',
								'/(?<=<ul>|<\/li>)\s*?(?=<\/ul>|<li>)/is'
							);
							$replace = array (
								'<b>$1</b>',
								'<i>$1</i>',
								'<u>$1</u>',
								'<s>$1</s>',
								'<ul>$1</ul>',
								'<li>$1</li>',
								'<ol>$1</ol>',
								'<font size="$1">$2</font>',
								'<span style="color:$1;">$2</span>',
								'<a href="$1" target="_blank" style="text-transform:none;">$3</a>',
								'<img src="$1" alt="" />',
								'<img width="$1" height="$2" src="$3" alt="" />',
								'<center>$1</center>',
								'<td>$1</td>',
								'<tr>$1</tr>',
								'<table>$1</table>',
								'<sup>$1</sup>',
								'<sub>$1</sub>',
								'<span style="display: block; text-align: right;">$1</span>',
								'<span style="display: block; text-align: justify;">$1</span>',
								''
							);
							$retVal = preg_replace($search, $replace, $retVal); // cleen for br

							break;
						default:
							break;
					}
				}
			}

			return $retVal;
		}
	/**
	 * Removes invalid XML
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
		public static function stripInvalidXml($value)
	{
		$ret = "";
		$current;
		if (empty($value))
		{
			return $ret;
		}

		$length = strlen($value);
		for ($i=0; $i < $length; $i++)
		{
			$current = ord($value[$i]);
			if (($current == 0x9) ||
				($current == 0xA) ||
				($current == 0xD) ||
				(($current >= 0x20) && ($current <= 0xD7FF)) ||
				(($current >= 0xE000) && ($current <= 0xFFFD)) ||
				(($current >= 0x10000) && ($current <= 0x10FFFF)))
			{
				$ret .= chr($current);
			}
			else
			{
				$ret .= " ";
			}
		}
		return $ret;
	}
		public static function getQuotedString($str){
			if (isset($str) && $str!=null){
				return '\'' . $str . '\'';
				//return '\'' . str_replace('%27', '\'', $str) . '\'';
			}
			return null;
		}

		public static function getJsonEncodeString($str){
			if (isset($str) && $str!=null){
				return json_encode($str);
			}
			return null;

		}

		public static function parseJsonDate($date, $format = 'd/m/Y') {
			date_default_timezone_set('UTC');
			//preg_match( '/([\d]{13})/', $date, $matches);
			preg_match( '/(\-?)([\d]{9,})/', $date, $matches);
			// Match the time stamp (microtime) and the timezone offset (may be + or -)
			$formatDate = 'd/m/Y';
			if (isset($format) && $format!=""){
				$formatDate = $format;
			}
			 if (count($matches)<2) {
				 echo $date;
			 }
			$date = date($formatDate, $matches[1].$matches[2]/1000 ); // convert to seconds from microseconds
			return $date;
		}

		public static function parseJsonDateTime($date, $format = 'd/m/Y') {
			date_default_timezone_set('UTC');
			return DateTime::createFromFormat($format, BFCHelper::parseJsonDate($date,$format),new DateTimeZone('UTC'));
		}
		public static function parseStringDateTime($date, $format = 'Y-m-d\TH:i:s') {
			date_default_timezone_set('UTC');
			return DateTime::createFromFormat($format, $date,new DateTimeZone('UTC'));
		}

		public static function parseArrayList($stringList, $fistDelimiter = ';', $secondDelimiter = '|'){
			$a = array();
			if(!empty($stringList)){
			foreach (explode($fistDelimiter, $stringList) as $aa) {
				list ($cKey, $cValue) = explode($secondDelimiter, $aa, 2);
				$a[$cKey] = $cValue;
			}
			}
			return $a;
		}

		public static function getDefaultCheckMode() {
			return self::$defaultCheckMode;
		}

		public static function getImagePath($type) {
			return self::$image_paths[$type];
		}

		public static function getImageUrlResized($type, $path = null, $resizedpath = null ) {
			if ($path == '' || $path===null)
				return '';
			$finalPath = self::$image_basePathCDN . COM_BOOKINGFORCONNECTOR_IMGURL;
			if (isset($type) && isset(self::$image_paths[$type])) {
				$finalPath .= self::$image_paths[$type] ;
				if (!empty($resizedpath)) {
						$pathfilename = basename($path);
						if (isset(self::$image_path_resized[$resizedpath])) {
							$path = str_replace($pathfilename, self::$image_path_resized[$resizedpath] . "/".$pathfilename ,$path);
						} else {
							$path = str_replace($pathfilename, $resizedpath . "/".$pathfilename ,$path);
						}
				}
				$finalPath .= $path;
			}

			return $finalPath;
		}

		public static function formatDistanceUnits($meters)
		{
			if ($meters >= 1000)
			{
				//round to .1
				$meters = number_format(floor($meters / 1000),1) . ' Km';
			}
			elseif ($meters > 0)
			{
				// round under 50m
				$meters =  $meters + (50 -  $meters % 50);
				$meters = number_format(floor($meters),0)  . ' m';
			}
			else
			{
				$meters = '';
			}
			return $meters;
		}

		public static function getImageUrl($type, $path = null, $resizepars = null ) {
			if ($path == '' || $path===null)
				return '';
			$finalPath = self::$image_basePath;
			if (isset($type) && isset(self::$image_paths[$type])) {
				$finalPath .= self::$image_paths[$type] . $path;
				if (isset($resizepars)) {
					// resize params manually added
					if (is_array($resizepars)) {
						$params = '';
						foreach ($resizepars as $param) {
							if ($params=='')
								$params .= '?';
							else
								$params .= '&';
							$params .= $param;
						}
						if ($params!='') {
							$finalPath .= $params;
						}
					} else { // resize params as predefined configuration
						if (isset(self::$image_resizes[$resizepars])) {
							$finalPath .= '?' . self::$image_resizes[$resizepars];
						}
					}
				}
			}

			return $finalPath;
		}

		public static function getDefaultParam($param) {
			switch (strtolower($param)) {
				case 'checkin':
					return DateTime::createFromFormat('d/m/Y',self::getStartDate(),new DateTimeZone('UTC'));
					//return new DateTime('UTC');
					break;
				case 'checkout':
					$co = DateTime::createFromFormat('d/m/Y',self::getStartDate(),new DateTimeZone('UTC'));
					//$co = new DateTime('UTC');
					return $co->modify(self::$defaultDaysSpan);
					break;
				case 'duration':
					return self::$defaultDuration;
					break;
				case 'extras':
					return '';
					break;
				case 'paxages':
					return '';
					break;
				case 'pricetype':
					return '';
					break;
				default:
					break;
			}
		}

		/* http://blog.amnuts.com/2011/04/08/sorting-an-array-of-objects-by-one-or-more-object-property/
		 *
		 * Sort an array of objects.
		 *
		 * You can pass in one or more properties on which to sort.  If a
		 * string is supplied as the sole property, or if you specify a
		 * property without a sort order then the sorting will be ascending.
		 *
		 * If the key of an array is an array, then it will sorted down to that
		 * level of node.
		 *
		 * Example usages:
		 *
		 * osort($items, 'size');
		 * osort($items, array('size', array('time' => SORT_DESC, 'user' => SORT_ASC));
		 * osort($items, array('size', array('user', 'forname'))
		 *
		 * @param array $array
		 * @param string|array $properties
		 *
		 */
		public static function osort(&$array, $properties) {
			if (is_string($properties)) {
				$properties = array($properties => SORT_ASC);
			}
			uasort($array, function($a, $b) use ($properties) {
				foreach($properties as $k => $v) {
					if (is_int($k)) {
						$k = $v;
						$v = SORT_ASC;
					}
					$collapse = function($node, $props) {
						if (is_array($props)) {
							foreach ($props as $prop) {
								$node = (!isset($node->$prop)) ? null : $node->$prop;
							}
							return $node;
						}else {
							return (!isset($node->$props)) ? null : $node->$props;
						}
					};
					$aProp = $collapse($a, $k);
					$bProp = $collapse($b, $k);
					if ($aProp != $bProp) {
						return ($v == SORT_ASC)
							? strnatcasecmp($aProp, $bProp)
							: strnatcasecmp($bProp, $aProp);
					}
				}
				return 0;
			});
		}

		public static function getCookie($cookieName, $defaultValue=null) {
//			$app = JFactory::getApplication();
//			$cookieValue = $app->input->cookie->get($cookieName, $defaultValue);
			$cookieValue =null;
			if (isset($_COOKIE[$cookieName])) {
				$cookieValue = $_COOKIE[$cookieName];
			}
			return $cookieValue;
		}

		public static function SetUniqueDeviceCookie($value = "") {
			$guid = uniqid("");
			if (!empty($value)) {
				$guid = $value;
			}
			$expire=time()+60*60*24*self::$TwoFactorAuthenticationDeviceExpiration;
			$ok = setcookie(self::$UserDeviceIdName, $guid, $expire,SITECOOKIEPATH, COOKIE_DOMAIN);
			return $guid;
		}

		public static function GetUniqueDeviceCookie() {
			$twofactorCookie = BFCHelper::getCookie(self::$UserDeviceIdName);
			return $twofactorCookie;
		}

		public static function SetTwoFactorCookie($id) {
			$expire=time()+60*60*24*self::$TwoFactorAuthenticationDeviceExpiration;
			$ok = setcookie(self::$TwoFactorCookieName, $id, $expire,SITECOOKIEPATH, COOKIE_DOMAIN);
		}

		public static function GetTwoFactorCookie() {
			$twofactorCookie = BFCHelper::getCookie(self::$TwoFactorCookieName);
			return $twofactorCookie;
		}

		public static function DeleteTwoFactorCookie() {
			setcookie( self::$TwoFactorCookieName, '', 0,SITECOOKIEPATH, COOKIE_DOMAIN);
			unset( $_COOKIE[self::$TwoFactorCookieName] );
		}


		public static function getLoginTwoFactor($email, $password, $twoFactorAuthCode, $twoFactorDeviceCode, $deviceCodeAuthCode, $deviceAuthToken = null) {
	//J->			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//J->			$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPortal;
			return $model->getLoginTwoFactor($email, $password, $twoFactorAuthCode, $twoFactorDeviceCode, $deviceCodeAuthCode, $deviceAuthToken);
		}

		public static function logoutUser($email, $deviceCodeAuthCode, $deviceAuthToken) {
	//J->			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//J->			$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPortal;
			return $model->logoutUser($email, $deviceCodeAuthCode, $deviceAuthToken);
		}

		public static function checkDeviceToken($email, $deviceCodeAuthCode, $deviceAuthToken) {
	//J->			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//J->			$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPortal;
			return $model->checkDeviceToken($email, $deviceCodeAuthCode, $deviceAuthToken);
		}

		public static function AddToFavourites($id) {
			$expire=time()+60*60*24*30;
			$counter = 1;
			$listFav = (string) $id;
			$varCook = BFCHelper::getCookie(self::$favouriteCookieName);
			if (isset($varCook))
			{
				$arr= explode(",", $varCook);
				if ( !self::IsInFavourites($id)){
					array_push($arr, $id);
				}
				$arr = array_filter( $arr );
				$counter = count($arr);
				$listFav = implode(",", $arr);
			}
			$config = JFactory::getConfig();
			$cookie_domain = $config->get('cookie_domain', '');
			$cookie_path = $config->get('cookie_path', '/');
			$ok = setcookie(self::$favouriteCookieName, $listFav, $expire, $cookie_path, '');
			//setcookie(self::$favouriteCookieName, $listFav, $expire, $cookie_path, $cookie_domain);
			return $counter;
		}

		public static function RemoveFromFavourites($id) {
			$expire=time()+60*60*24*30;
			$listFav = (string) $id;
			$counter = 0;
			$varCook = BFCHelper::getCookie(self::$favouriteCookieName);
			if (isset($varCook))
			{
				$arr= explode(",", $varCook);
				if(($key = array_search($id, $arr)) !== false) {
					unset($arr[$key]);
				}
				$arr = array_filter( $arr );
				$counter = count($arr);
				$listFav = implode(",", $arr);
			}
			$config = JFactory::getConfig();
			$cookie_domain = $config->get('cookie_domain', '');
			$cookie_path = $config->get('cookie_path', '/');
			setcookie(self::$favouriteCookieName, $listFav, $expire, $cookie_path, '');
			//setcookie(self::$favouriteCookieName, $listFav, $expire);
			return $counter;
		}

		public static function IsInFavourites($id) {
			$varCook = BFCHelper::getCookie(self::$favouriteCookieName);
			if (isset($varCook))
			{
				$arr= explode(",", $varCook);
				return in_array($id, $arr);
			}
			return false;
		}

		public static function CountFavourites() {
			$varCook = BFCHelper::getCookie(self::$favouriteCookieName);
			if (isset($varCook))
			{
				$arr= explode(",", $varCook);
				return count(array_filter($arr));
			}
			return 0;
		}

		public static function GetFavourites() {
			$varCook = BFCHelper::getCookie(self::$favouriteCookieName);
			if (isset($varCook))
			{
				$arr= explode(",", $varCook);
				return $arr;
			}
			return null;
		}


	//for analytics
			public static function AddToCookieOrders($id) {
				$expire=time()+60*60*24*30;
				$counter = 1;
				$lisTordersCookie = (string) $id;
				$varCook = BFCHelper::getCookie(self::$ordersCookieName);
				if (isset($varCook))
				{
					$arr= explode("_", $varCook);
					if ( !self::IsInCookieOrders($id)){
						array_push($arr, (string)$id);
					}
					$arr = array_filter( $arr );
					$counter = count($arr);
					$lisTordersCookie = (string)implode("_", $arr);
				}
//				$config = JFactory::getConfig();
//				$cookie_domain = $config->get('cookie_domain', '');
//				$cookie_path = $config->get('cookie_path', '/');
//				$ok = setcookie(self::$ordersCookieName, $lisTordersCookie, $expire, $cookie_path, '');
				$ok = setcookie(self::$ordersCookieName, $lisTordersCookie, $expire);
				return $counter;
			}

			public static function IsInCookieOrders($id) {
				$varCook = BFCHelper::getCookie(self::$ordersCookieName);


				if (isset($varCook))
				{
					$arr= explode("_", $varCook);
					return in_array($id, $arr);
				}
				return false;
			}

		public static function setSearchOnSellParamsSession($params) {
			$sessionkey = 'searchonsell.params';
	//		$_SESSION[$sessionkey] = $params;
			$pars = self::setSession($sessionkey, $params, 'com_bookingforconnector'); // $_SESSION[$sessionkey];
		}

		public static function getSearchOnSellParamsSession() {
			$sessionkey = 'searchonsell.params';
	//		$session = JFactory::getSession();
			$pars = self::getSession($sessionkey, '', 'com_bookingforconnector'); // $_SESSION[$sessionkey];
			return $pars;
		}

		public static function setSearchMerchantParamsSession($params) {
			$sessionkey = 'searchmerchant.params';
			$pars = array();
			$pars['merchantCategoryId'] = !empty($params['merchantCategoryId']) ? $params['merchantCategoryId']: 0;
			if(isset($params['searchid'])){
				$pars['searchid'] = $params['searchid'];
			}
			if(isset($params['newsearch'])){
				$pars['newsearch'] = $params['newsearch'];
			}
			if(isset($params['points'])){
				$pars['points'] = $params['points'];
			}
			$pars['locationzone'] = !empty($params['locationzone']) ? $params['locationzone']: "";
			$pars['locationzones'] = !empty($params['locationzones']) ? $params['locationzones']: "";
			$pars['stateIds'] = !empty($params['stateIds']) ? $params['stateIds']: "";
			$pars['regionIds'] = !empty($params['regionIds']) ? $params['regionIds']: "";
			$pars['cityIds'] = !empty($params['cityIds']) ? $params['cityIds']: "";
			$pars['zoneIds'] = !empty($params['zoneIds']) ? $params['zoneIds']: "";
			$pars['cultureCode'] = !empty($params['cultureCode']) ? $params['cultureCode']: "";
			$pars['merchantTagIds'] = !empty($params['merchantTagIds']) ? $params['merchantTagIds']:"";
			$pars['tags'] = !empty($params['tags']) ? $params['tags']:"";
			$pars['rating'] = !empty($params['rating']) ? $params['rating']:"";
			$pars['filters'] = !empty($params['filters']) ? $params['filters']: "";
			self::setSession($sessionkey, $pars, 'com_bookingforconnector');
		}
		public static function getSearchMerchantParamsSession() {
			$sessionkey = 'searchmerchant.params';
			$pars = self::getSession($sessionkey, '', 'com_bookingforconnector');
			return $pars;
		}
		public static function setFilterSearchMerchantParamsSession($paramsfilters) {
			$sessionkey = 'searchmerchant.filterparams';
			self::setSession($sessionkey, $paramsfilters, 'com_bookingforconnector');
		}

		public static function getFilterSearchMerchantParamsSession() {
			$sessionkey = 'searchmerchant.filterparams';
			$paramsfilters = self::getSession($sessionkey, '', 'com_bookingforconnector');
			return $paramsfilters;
		}

		public static function setEnabledFilterSearchMerchantParamsSession($paramsfilters) {
			$sessionkey = 'searchmerchant.enabledfilterparams';
			self::setSession($sessionkey, $paramsfilters, 'com_bookingforconnector');
		}

		public static function getEnabledFilterSearchMerchantParamsSession() {
			$sessionkey = 'searchmerchant.enabledfilterparams';
			$paramsfilters = self::getSession($sessionkey, '', 'com_bookingforconnector');
			return $paramsfilters;
		}
		public static function setFirstFilterSearchMerchantParamsSession($paramsfilters) {
			$sessionkey = 'searchmerchant.firstfilterparams';
			self::setSession($sessionkey, $paramsfilters, 'com_bookingforconnector');
		}

		public static function getFirstFilterSearchMerchantParamsSession() {
			$sessionkey = 'searchmerchant.firstfilterparams';
			$paramsfilters = self::getSession($sessionkey, '', 'com_bookingforconnector');
			return $paramsfilters;
		}

		public static function setSearchParamsSession($params, $sessionkey = 'search.params') {
			$pars = array();

			if(isset($params['checkin'])){
				$pars['checkin'] = $params['checkin'];
			}
			if(isset($params['checkout'])){
				$pars['checkout'] = $params['checkout'];
			}
			if(isset($params['duration'])){
				$pars['duration'] = $params['duration'];
			}
			if(isset($params['paxes'])){
				$pars['paxes'] = $params['paxes'];
			}
			if(isset($params['paxages'])){
				$pars['paxages'] = $params['paxages'];
			}
			$pars['minqt'] = !empty($params['minqt']) ? $params['minqt']: 1;
			$pars['maxqt'] = !empty($params['maxqt']) ? $params['maxqt']: 1;

			$pars['onlystay'] = !empty($params['onlystay']) ? $params['onlystay']: 1;
			$pars['getallresults'] = !empty($params['getallresults']) ? $params['getallresults']: 0;
			$pars['checkFullPeriod'] = !empty($params['checkFullPeriod']) ? $params['checkFullPeriod']: 0;
			$pars['checkAvailability'] = !empty($params['checkAvailability']) ? $params['checkAvailability']: 0;
			$pars['checkStays'] = !empty($params['checkStays']) ? $params['checkStays']: 0;

			$pars['searchtypetab'] = !empty($params['searchtypetab']) ? $params['searchtypetab']: "0";
			$pars['masterTypeId'] = !empty($params['masterTypeId']) ? $params['masterTypeId']: "0";
			$pars['merchantResults'] = !empty($params['merchantResults']) ? $params['merchantResults']: 0;
			$pars['merchantCategoryId'] = !empty($params['merchantCategoryId']) ? $params['merchantCategoryId']: 0;
			$pars['zoneId'] = !empty($params['zoneId']) ? $params['zoneId']: 0;
			$pars['cityId'] = !empty($params['cityId']) ? $params['cityId']: 0;
			$pars['locationzone'] = !empty($params['locationzone']) ? $params['locationzone']: "";
			$pars['locationzones'] = !empty($params['locationzones']) ? $params['locationzones']: "";
			$pars['zoneIds'] = !empty($params['zoneIds']) ? $params['zoneIds']: "";
			$pars['cultureCode'] = !empty($params['cultureCode']) ? $params['cultureCode']: "";
			$pars['filters'] = !empty($params['filters']) ? $params['filters']: "";
			$pars['resourceName'] = !empty($params['resourceName']) ? $params['resourceName']: "";
			$pars['refid'] = !empty($params['refid']) ? $params['refid']: "";
			$pars['pricerange'] = !empty($params['pricerange']) ? $params['pricerange']: 0;
			$pars['bookableonly'] = !empty($params['bookableonly']) ? $params['bookableonly']: 0;
			$pars['resourcegroupsResults'] = !empty($params['resourcegroupsResults']) ? $params['resourcegroupsResults']: 0;
			$pars['productTagIds'] = !empty($params['productTagIds']) ? $params['productTagIds']:"";
			$pars['merchantTagIds'] = !empty($params['merchantTagIds']) ? $params['merchantTagIds']:"";
			$pars['groupTagsIds'] = !empty($params['groupTagsIds']) ? $params['groupTagsIds']:"";
			$pars['merchantIds'] = !empty($params['merchantIds']) ? $params['merchantIds']:"";
			$pars['groupresourceIds'] = !empty($params['groupresourceIds']) ? $params['groupresourceIds']:"";
 			$pars['searchterm'] = !empty($params['searchterm']) ? $params['searchterm']:"";
 			$pars['searchTermValue'] = !empty($params['searchTermValue']) ? $params['searchTermValue']:"";
 			$pars['dropoff'] = !empty($params['dropoff']) ? $params['dropoff']:"";
 			$pars['dropoffValue'] = !empty($params['dropoffValue']) ? $params['dropoffValue']:"";
 			$pars['filter_order'] = !empty($params['filter_order']) ? $params['filter_order']:"";
 			$pars['filter_order_Dir'] = !empty($params['filter_order_Dir']) ? $params['filter_order_Dir']:"";
 			$pars['getBaseFiltersFor'] = !empty($params['getBaseFiltersFor']) ? $params['getBaseFiltersFor']:"";

			$pars['variationPlanIds'] = !empty($params['variationPlanIds']) ? $params['variationPlanIds']:"";
			$pars['dateselected'] = !empty($params['dateselected']) ? $params['dateselected']:0;

			if(isset($params['merchantId'])){
				$pars['merchantId'] = $params['merchantId'];
			}
			if(isset($params['resourcegroupId'])){
				$pars['resourcegroupId'] = $params['resourcegroupId'];
			}
			if(isset($params['resourceId'])){
				$pars['resourceId'] = $params['resourceId'];
			}

			if(!empty($params['availabilitytype'])){
				$pars['availabilitytype'] = $params['availabilitytype'];
			}
			if(isset($params['itemtypes'])){
				$pars['itemtypes'] = $params['itemtypes'];
			}
			if(isset($params['groupresulttype'])){
				$pars['groupresulttype'] = $params['groupresulttype'];
			}
			if(isset($params['searchid'])){
				$pars['searchid'] = $params['searchid'];
			}
			if(isset($params['newsearch'])){
				$pars['newsearch'] = $params['newsearch'];
			}
			if(isset($params['stateIds'])){
				$pars['stateIds'] = $params['stateIds'];
			}
			if(isset($params['regionIds'])){
				$pars['regionIds'] = $params['regionIds'];
			}
			if(isset($params['cityIds'])){
				$pars['cityIds'] = $params['cityIds'];
			}
			if(isset($params['points'])){
				$pars['points'] = $params['points'];
			}

			self::setSession($sessionkey, $pars, 'com_bookingforconnector');

		}

		public static function getSearchParamsSession($sessionkey = 'search.params') {
			$pars = self::getSession($sessionkey, '', 'com_bookingforconnector');
			$pars = unserialize(serialize($pars));
			return $pars;
		}

		public static function setFilterSearchParamsSession($paramsfilters, $sessionkeysearch = 'search.params') {
            //$sessionkey = 'search.filterparams';
			$sessionkey = $sessionkeysearch .'.filter';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getFilterSearchParamsSession($sessionkeysearch = 'search.params') {
            //$sessionkey = 'search.filterparams';
			$sessionkey = $sessionkeysearch .'.filter';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array();
			$paramsfilters = unserialize(serialize($paramsfilters));
			return $paramsfilters;
		}

		public static function setFirstFilterSearchParamsSession($paramsfilters, $sessionkeysearch = 'search.params') {
            //$sessionkey = 'search.firstfilterparams';
			$sessionkey = $sessionkeysearch .'.firstfilter';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getFirstFilterSearchParamsSession($sessionkeysearch = 'search.params') {
            //$sessionkey = 'search.firstfilterparams';
			$sessionkey = $sessionkeysearch .'.firstfilter';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array() ;
			return $paramsfilters;
		}

		public static function setEnabledFilterSearchParamsSession($paramsfilters, $sessionkeysearch = 'search.params') {
            //$sessionkey = 'search.enabledfilterparams';
			$sessionkey = $sessionkeysearch .'.enabledfilter';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getEnabledFilterSearchParamsSession($sessionkeysearch = 'search.params') {
            //$sessionkey = 'search.enabledfilterparams';
			$sessionkey = $sessionkeysearch .'.enabledfilter';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array() ;
			return $paramsfilters;
		}
// **--------------- events search
		public static function setSearchEventParamsSession($params) {
			$sessionkey = 'searchevent.params';
			$pars = array();

			if(isset($params['checkin'])){
				$pars['checkin'] = $params['checkin'];
			}
			if(isset($params['checkout'])){
				$pars['checkout'] = $params['checkout'];
			}

			$pars['stateIds'] = !empty($params['stateIds']) ? $params['stateIds']: "";
			$pars['regionIds'] = !empty($params['regionIds']) ? $params['regionIds']: "";
			$pars['cityIds'] = !empty($params['cityIds']) ? $params['cityIds']: "";
			$pars['zoneIds'] = !empty($params['zoneIds']) ? $params['zoneIds']: "";
			$pars['eventId'] = !empty($params['eventId']) ? $params['eventId']:0;
			$pars['categoryIds'] = !empty($params['categoryIds']) ? $params['categoryIds']: "";
			$pars['tagids'] = !empty($params['tagids']) ? $params['tagids']:"";
			$pars['pointOfInterestId'] = !empty($params['pointOfInterestId']) ? $params['pointOfInterestId']:0;
			$pars['merchantIds'] = !empty($params['merchantIds']) ? $params['merchantIds']:"";
			$pars['searchterm'] = !empty($params['searchterm']) ? $params['searchterm']: "";

			$pars['eventName'] = !empty($params['eventName']) ? $params['eventName']: "";

			$pars['cultureCode'] = !empty($params['cultureCode']) ? $params['cultureCode']: "";
			if(isset($params['merchantId'])){
				$pars['merchantId'] = $params['merchantId'];
			}
			if(isset($params['points'])){
				$pars['points'] = $params['points'];
			}
			if(isset($params['searchid'])){
				$pars['searchid'] = $params['searchid'];
			}
			$pars['filters'] = !empty($params['filters']) ? $params['filters']: "";
			$pars['getFilters'] = !empty($params['getFilters']) ? $params['getFilters']: 1;
			if(isset($params['newsearch'])){
				$pars['newsearch'] = $params['newsearch'];
			}
			self::setSession($sessionkey, $pars, 'com_bookingforconnector');

		}

		public static function getSearchEventParamsSession() {
			$sessionkey = 'searchevent.params';
			$pars = self::getSession($sessionkey, '', 'com_bookingforconnector');
			$pars = unserialize(serialize($pars));
			return $pars;
		}
		public static function getSearchParamsSessionforEvent() {
			$sessionkey = 'searchforevent.params';
			$pars = self::getSession($sessionkey, '', 'com_bookingforconnector');
			$pars = unserialize(serialize($pars));
			return $pars;
		}
		public static function setSearchParamsSessionforEvent($params) {
			$sessionkey = 'searchforevent.params';
			$pars = array();

			if(isset($params['checkin'])){
				$pars['checkin'] = $params['checkin'];
			}
			if(isset($params['checkout'])){
				$pars['checkout'] = $params['checkout'];
			}
			if(isset($params['duration'])){
				$pars['duration'] = $params['duration'];
			}
			if(isset($params['calculateperson'])){
				$pars['calculateperson'] = $params['calculateperson'];
			}
			if(isset($params['paxes'])){
				$pars['paxes'] = $params['paxes'];
			}
			if(isset($params['paxages'])){
				$pars['paxages'] = $params['paxages'];
			}
			$pars['minqt'] = !empty($params['minqt']) ? $params['minqt']: 1;
			$pars['maxqt'] = !empty($params['maxqt']) ? $params['maxqt']: 1;

			$pars['onlystay'] = !empty($params['onlystay']) ? $params['onlystay']: 1;
			$pars['getallresults'] = !empty($params['getallresults']) ? $params['getallresults']: 0;
			$pars['checkFullPeriod'] = !empty($params['checkFullPeriod']) ? $params['checkFullPeriod']: 0;

			$pars['searchtypetab'] = !empty($params['searchtypetab']) ? $params['searchtypetab']: "0";
			$pars['masterTypeId'] = !empty($params['masterTypeId']) ? $params['masterTypeId']: "0";
			$pars['merchantResults'] = !empty($params['merchantResults']) ? $params['merchantResults']: 0;
			$pars['merchantCategoryId'] = !empty($params['merchantCategoryId']) ? $params['merchantCategoryId']: 0;
			$pars['zoneId'] = !empty($params['zoneId']) ? $params['zoneId']: 0;
			$pars['cityId'] = !empty($params['cityId']) ? $params['cityId']: 0;
			$pars['locationzone'] = !empty($params['locationzone']) ? $params['locationzone']: "";
			$pars['locationzones'] = !empty($params['locationzones']) ? $params['locationzones']: "";
			$pars['zoneIds'] = !empty($params['zoneIds']) ? $params['zoneIds']: "";
			$pars['cultureCode'] = !empty($params['cultureCode']) ? $params['cultureCode']: "";
			$pars['filters'] = !empty($params['filters']) ? $params['filters']: "";
			$pars['resourceName'] = !empty($params['resourceName']) ? $params['resourceName']: "";
			$pars['refid'] = !empty($params['refid']) ? $params['refid']: "";
			$pars['pricerange'] = !empty($params['pricerange']) ? $params['pricerange']: 0;
			$pars['bookableonly'] = !empty($params['bookableonly']) ? $params['bookableonly']: 0;
			$pars['resourcegroupsResults'] = !empty($params['resourcegroupsResults']) ? $params['resourcegroupsResults']: 0;
			$pars['productTagIds'] = !empty($params['productTagIds']) ? $params['productTagIds']:"";
			$pars['merchantTagIds'] = !empty($params['merchantTagIds']) ? $params['merchantTagIds']:"";
			$pars['groupTagsIds'] = !empty($params['groupTagsIds']) ? $params['groupTagsIds']:"";
			$pars['merchantIds'] = !empty($params['merchantIds']) ? $params['merchantIds']:"";
			$pars['groupresourceIds'] = !empty($params['groupresourceIds']) ? $params['groupresourceIds']:"";
 			$pars['searchterm'] = !empty($params['searchterm']) ? $params['searchterm']:"";
 			$pars['searchTermValue'] = !empty($params['searchTermValue']) ? $params['searchTermValue']:"";
 			$pars['filter_order'] = !empty($params['filter_order']) ? $params['filter_order']:"";
 			$pars['filter_order_Dir'] = !empty($params['filter_order_Dir']) ? $params['filter_order_Dir']:"";
 			$pars['getBaseFiltersFor'] = !empty($params['getBaseFiltersFor']) ? $params['getBaseFiltersFor']:"";

			$pars['variationPlanIds'] = !empty($params['variationPlanIds']) ? $params['variationPlanIds']:"";

			if(isset($params['merchantId'])){
				$pars['merchantId'] = $params['merchantId'];
			}
			if(isset($params['resourcegroupId'])){
				$pars['resourcegroupId'] = $params['resourcegroupId'];
			}
			if(isset($params['resourceId'])){
				$pars['resourceId'] = $params['resourceId'];
			}

			if(!empty($params['availabilitytype'])){
				$pars['availabilitytype'] = $params['availabilitytype'];
			}
			if(isset($params['itemtypes'])){
				$pars['itemtypes'] = $params['itemtypes'];
			}
			if(isset($params['groupresulttype'])){
				$pars['groupresulttype'] = $params['groupresulttype'];
			}
			if(isset($params['searchid'])){
				$pars['searchid'] = $params['searchid'];
			}
			if(isset($params['newsearch'])){
				$pars['newsearch'] = $params['newsearch'];
			}
			if(isset($params['stateIds'])){
				$pars['stateIds'] = $params['stateIds'];
			}
			if(isset($params['regionIds'])){
				$pars['regionIds'] = $params['regionIds'];
			}
			if(isset($params['cityIds'])){
				$pars['cityIds'] = $params['cityIds'];
			}
			if(isset($params['points'])){
				$pars['points'] = $params['points'];
			}
			self::setSession($sessionkey, $pars, 'com_bookingforconnector');

		}

		public static function setFilterSearchEventParamsSession($paramsfilters) {
			$sessionkey = 'searchevent.filterparams';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getFilterSearchEventParamsSession() {
			$sessionkey = 'searchevent.filterparams';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array();
			$paramsfilters = unserialize(serialize($paramsfilters));
			return $paramsfilters;
		}

		public static function setEnabledFilterSearchEventParamsSession($paramsfilters) {
			$sessionkey = 'searchevent.enabledfilterparams';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getEnabledFilterSearchEventParamsSession() {
			$sessionkey = 'searchevent.enabledfilterparams';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array() ;
			return $paramsfilters;
		}
		public static function setFirstFilterSearchEventParamsSession($paramsfilters) {
			$sessionkey = 'searchevent.firstfilterparams';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getFirstFilterSearchEventParamsSession() {
			$sessionkey = 'searchevent.firstfilterparams';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array() ;
			return $paramsfilters;
		}
//**--------filter details
		public static function setFilterDetailsParamsSession($paramsfilters, $currkey) {
			$sessionkey = 'details.filterparams'.$currkey;
			self::setSession($sessionkey, $paramsfilters, 'com_bookingforconnector');
		}

		public static function getFilterDetailsParamsSession($currkey) {
			$sessionkey = 'details.filterparams'.$currkey;
			$paramsfilters = self::getSession($sessionkey, '', 'com_bookingforconnector');
			return $paramsfilters;
		}

		public static function setEnabledFilterDetailsParamsSession($paramsfilters, $currkey) {
			$sessionkey = 'details.enabledfilterparams'.$currkey;
			self::setSession($sessionkey, $paramsfilters, 'com_bookingforconnector');
		}

		public static function getEnabledFilterDetailsParamsSession($currkey) {
			$sessionkey = 'details.enabledfilterparams'.$currkey;
			$paramsfilters = self::getSession($sessionkey, '', 'com_bookingforconnector');
			return $paramsfilters;
		}
		public static function setFirstFilterDetailsParamsSession($paramsfilters, $currkey) {
			$sessionkey = 'details.firstfilterparams'.$currkey;
			self::setSession($sessionkey, $paramsfilters, 'com_bookingforconnector');
		}

		public static function getFirstFilterDetailsParamsSession($currkey) {
			$sessionkey = 'details.firstfilterparams'.$currkey;
			$paramsfilters = self::getSession($sessionkey, '', 'com_bookingforconnector');
			return $paramsfilters;
		}
//**--------filtee details

//**--------filter details  MapSells

		public static function setFilterSearchMapSellsParamsSession($paramsfilters) {
			$sessionkey = 'searchmapsells.filterparams';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getFilterSearchMapSellsParamsSession() {
			$sessionkey = 'searchmapsells.filterparams';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array();
			$paramsfilters = unserialize(serialize($paramsfilters));
			return $paramsfilters;
		}

		public static function setEnabledFilterSearchMapSellsParamsSession($paramsfilters) {
			$sessionkey = 'searchmapsells.enabledfilterparams';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getEnabledFilterSearchMapSellsParamsSession() {
			$sessionkey = 'searchmapsells.enabledfilterparams';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array() ;
			return $paramsfilters;
		}
		public static function setFirstFilterSearchMapSellsParamsSession($paramsfilters) {
			$sessionkey = 'searchmapsells.firstfilterparams';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getFirstFilterSearchMapSellsParamsSession() {
			$sessionkey = 'searchmapsells.firstfilterparams';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array() ;
			return $paramsfilters;
		}
//**--------filter details  MapSells

// **--------------- POI search
		public static function setSearchPoiParamsSession($params) {
			$sessionkey = 'searchpoi.params';
			$pars = array();
			$pars['stateIds'] = !empty($params['stateIds']) ? $params['stateIds']: "";
			$pars['regionIds'] = !empty($params['regionIds']) ? $params['regionIds']: "";
			$pars['cityIds'] = !empty($params['cityIds']) ? $params['cityIds']: "";
			$pars['zoneIds'] = !empty($params['zoneIds']) ? $params['zoneIds']: "";
			$pars['categoryIds'] = !empty($params['categoryIds']) ? $params['categoryIds']: "";
			$pars['tagids'] = !empty($params['tagids']) ? $params['tagids']:"";
			$pars['searchterm'] = !empty($params['searchterm']) ? $params['searchterm']: "";

			$pars['poiName'] = !empty($params['poiName']) ? $params['poiName']: "";

			$pars['cultureCode'] = !empty($params['cultureCode']) ? $params['cultureCode']: "";
			if(isset($params['points'])){
				$pars['points'] = $params['points'];
			}
			if(isset($params['searchid'])){
				$pars['searchid'] = $params['searchid'];
			}
			$pars['filters'] = !empty($params['filters']) ? $params['filters']: "";
			$pars['getFilters'] = !empty($params['getFilters']) ? $params['getFilters']: 1;
			if(isset($params['newsearch'])){
				$pars['newsearch'] = $params['newsearch'];
			}
			self::setSession($sessionkey, $pars, 'com_bookingforconnector');

		}

		public static function getSearchPoiParamsSession() {
			$sessionkey = 'searchpoi.params';
			$pars = self::getSession($sessionkey, '', 'com_bookingforconnector');
			$pars = unserialize(serialize($pars));
			return $pars;
		}

		public static function setFilterSearchPoiParamsSession($paramsfilters) {
			$sessionkey = 'searchpoi.filterparams';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getFilterSearchPoiParamsSession() {
			$sessionkey = 'searchpoi.filterparams';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array();
			$paramsfilters = unserialize(serialize($paramsfilters));
			return $paramsfilters;
		}

		public static function setEnabledFilterSearchPoiParamsSession($paramsfilters) {
			$sessionkey = 'searchpoi.enabledfilterparams';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getEnabledFilterSearchPoiParamsSession() {
			$sessionkey = 'searchpoi.enabledfilterparams';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array() ;
			return $paramsfilters;
		}
		public static function setFirstFilterSearchPoiParamsSession($paramsfilters) {
			$sessionkey = 'searchpoi.firstfilterparams';
			$_SESSION[$sessionkey] = $paramsfilters;
		}

		public static function getFirstFilterSearchPoiParamsSession() {
			$sessionkey = 'searchpoi.firstfilterparams';
			$paramsfilters = isset($_SESSION[$sessionkey]) ? $_SESSION[$sessionkey] : array() ;
			return $paramsfilters;
		}

		public static function setState($stateObj, $key, $namespace = null) {
			if (isset($namespace)) {
				$key = $namespace . '.' . $key;
			}
			self::$currentState[$key] = $stateObj;
		}

		public static function getState($key, $namespace = null) {
			if (isset($namespace)) {
				$key = $namespace . '.' . $key;
			}
			if (isset(self::$currentState[$key])) {
				return self::$currentState[$key];
			}
			return null;
		}

		public static function orderBy($a, $b, $ordering, $direction) {
			return ($a->$ordering < $b->$ordering) ?
			(
				($direction == 'desc')
					? 1
					: -1
			) :
			(
				($a->$ordering > $b->$ordering)
					?	(
							($direction == 'desc')
								? -1
								: 1
						)
					: 0
			);
		}

		public static function orderByStay($a, $b, $direction) {
			return ($a->Resources[0]->TotalPrice < $b->Resources[0]->TotalPrice) ?
			(
				($direction == 'desc')
					? 1
					: -1
			) :
			(
				($a->Resources[0]->TotalPrice > $b->Resources[0]->TotalPrice)
					?	(
							($direction == 'desc')
								? -1
								: 1
						)
					: 0
			);
		}


		public static function orderByDiscount($a, $b, $direction) {
			return ($a->Resources[0]->TotalPrice - $a->Resources[0]->Price < $b->Resources[0]->TotalPrice - $b->Resources[0]->Price) ?
			(
				($direction == 'desc')
					? 1
					: -1
			) :
			(
				($a->Resources[0]->TotalPrice - $a->Resources[0]->Price > $b->Resources[0]->TotalPrice - $b->Resources[0]->Price)
					?	(
							($direction == 'desc')
								? -1
								: 1
						)
					: 0
			);
		}
			public static function orderBySingleDiscount($a, $b, $direction) {
			return ($a->TotalPrice - $a->Price < $b->TotalPrice - $b->Price) ?
			(
				($direction == 'desc')
					? 1
					: -1
			) :
			(
				($a->TotalPrice - $a->Price > $b->TotalPrice - $b->Price)
					?	(
							($direction == 'desc')
								? -1
								: 1
						)
					: 0
			);
		}


		public static function getStayParam($param, $default= null, $getFromSession = true) {
			date_default_timezone_set('UTC');
			$pars = self::getSearchParamsSession();

			switch (strtolower($param)) {
				case 'checkin':
					$strCheckin = isset($_REQUEST['checkin']) ? $_REQUEST['checkin'] :null ;
					if (($strCheckin == null || $strCheckin == '') && $getFromSession && (isset($pars['checkin']) && $pars['checkin'] != null && $pars['checkin'] != '')) {
						return clone $pars['checkin'];
//						return null;
					}
					$checkin = null;
					if (strpos($strCheckin,"/")!== false) {
						$checkin = DateTime::createFromFormat('d/m/Y',$strCheckin,new DateTimeZone('UTC'));
					}else {
						$checkin = DateTime::createFromFormat('YmdHis',$strCheckin,new DateTimeZone('UTC'));
					}

					if ($checkin===false && isset($default)) {
						$checkin = $default;
					}
					$strCheckinTime = isset($_REQUEST['checkintime']) ? $_REQUEST['checkintime'] :"00:00" ;
					if (!empty($checkin) && strpos($strCheckinTime,":")!== false) {
						$checkinTime = explode(':',$strCheckinTime.":0");
					    $checkin->setTime((int)$checkinTime[0], (int)$checkinTime[1]);
					}

					return $checkin;
					break;
				case 'checkout':
					$strCheckout =  isset($_REQUEST['checkout']) ? $_REQUEST['checkout'] :null ;
					if (($strCheckout == null || $strCheckout == '') && $getFromSession && (isset($pars['checkout']) && $pars['checkout'] != null && $pars['checkout'] != '')) {
						return clone $pars['checkout'];
//						return null;
					}
					$checkout = DateTime::createFromFormat('d/m/Y',$strCheckout,new DateTimeZone('UTC'));
					if ($checkout===false && isset($default)) {
						$checkout = $default;
					}
					$strCheckoutTime = isset($_REQUEST['checkouttime']) ? $_REQUEST['checkouttime'] :"00:00" ;

					if (!empty($checkout) && strpos($strCheckoutTime,":")!== false) {
						$checkoutTime = explode(':',$strCheckoutTime.":0");
					    $checkout->setTime((int)$checkoutTime[0], (int)$checkoutTime[1]);
					}

					return $checkout;

					break;
				case 'duration':
//					$currcheckin = self::getStayParam('checkin', null);
					$currcheckin = self::getStayParam('checkin', new DateTime('UTC'));
					if (!empty($currcheckin)) {
						$ci = self::getStayParam('checkin', new DateTime('UTC'));
						$dco = new DateTime('UTC');
						$co = self::getStayParam('checkout', $dco->modify('+7 days'));
						$interval = $co->diff($ci);
	//					return $interval->d;
						return $interval->format("%a");					    
					}
					return 0;

					break;
				case 'extras':
					$extraVar =  isset($_REQUEST['extras']) ? $_REQUEST['extras'] : '';
					$extras = "";
					if (!empty($extraVar)){
					$extras = implode('|',
						array_filter($extraVar, function($var) {
								$vals = explode(':', $var);
								if (count($vals) < 2 || $vals[1] == '') return false;
								return true;
							})
						);
					}
					return $extras;
					break;
				case 'packages':
					$packagesVar = isset($_REQUEST['packages']) ? $_REQUEST['packages'] : '';
					$packages = "";
					if (!empty($packagesVar)){
					$packages = implode('|',
						array_filter($packagesVar, function($var) {
								$vals = explode(':', $var);
								if (count($vals) < 3 || $vals[2] == 0 || $vals[1] == 0) return false;
								return true;
							})
						);
					}
					return $packages;
					break;
				case 'selectedprices':
					$extras = implode('|',
						array_filter($_REQUEST['extras'], function($var) {
							$vals = explode(':', $var);
							if (count($vals) < 2 || $vals[1] == '') return false;
							return true;
						})
					);
					return $extras;
					break;
				case 'pckpaxages':
					$currPaxages = isset($_REQUEST['paxages']) ? $_REQUEST['paxages'] : '';
					return $currPaxages;
					break;
				case 'paxages':
					$strAges = array();
					$reqAges = isset($_REQUEST['paxages']) ? $_REQUEST['paxages'] : '';
					if ($reqAges=='') {
						$adults = isset($_REQUEST['adults']) ? $_REQUEST['adults'] : (isset($_REQUEST['adultssel']) ? $_REQUEST['adultssel'] : self::$defaultAdultsQt);
						$children = isset($_REQUEST['children']) ? $_REQUEST['children'] : (isset($_REQUEST['childrensel']) ? $_REQUEST['childrensel'] : 0);
						$seniores = isset($_REQUEST['seniores']) ? $_REQUEST['seniores'] : (isset($_REQUEST['senioressel']) ? $_REQUEST['senioressel'] : 0);
						if (($adults == null || $adults == '') && ($children == null || $children == '') && (isset($pars['paxages']) && $pars['paxages'] != null && $pars['paxages'] != '')) {
							return array_slice($pars['paxages'],0);
						}
						for ($i = 0; $i < $adults; $i++) {
							$strAges[] = self::$defaultAdultsAge;
						}
						for ($i = 0; $i < $seniores; $i++) {
							$strAges[] = self::$defaultSenioresAge;
						}
						if ($children > 0) {
							for ($i = 0;$i < $children; $i++) {
								$age = !empty($_REQUEST['childagessel'.($i+1)]) ? $_REQUEST['childagessel'.($i+1)] : $_REQUEST['childages'.($i+1)];
								if ($age < self::$defaultAdultsAge) {
									$strAges[] = $age;
								}
							}
						}
					}else{
						$strAges = explode(",", $reqAges);
					}
					return $strAges;
					break;
				case 'pricetype':
					return isset($_REQUEST['pricetype']) ? $_REQUEST['pricetype'] : '';
					break;
				case 'pricerange':
					return isset($_REQUEST['pricerange']) ? $_REQUEST['pricerange'] : 0;
					break;
				case 'rateplanid':
					return isset($_REQUEST['pricetype']) ? $_REQUEST['pricetype'] : 0;
					break;
				case 'variationplanid':
					return isset($_REQUEST['variationPlanId']) ? $_REQUEST['variationPlanId'] : '';
					break;
				case 'state':
					return isset($_REQUEST['state']) ? $_REQUEST['state'] : '';
				default:
					break;
			}
			return null;
		}

		public static function convertTotal($x){
			switch($x){
				case $x < 3:
					$y = 0;
					break;
				case $x < 4:
					$y = 1;
					break;
				case $x < 5:
					$y = 2;
					break;
				case $x <= 5.5:
					$y = 3;
					break;
				case $x < 6:
					$y = 4;
					break;
				case $x < 7:
					$y = 5;
					break;
				case $x < 8:
					$y = 6;
					break;
				case $x <= 8.5:
					$y = 7;
					break;
				case $x < 9:
					$y = 8;
					break;
				case $x < 10:
					$y = 9;
					break;
				case $x == 10:
					$y = 10;
					break;
				default:
					$y = 4;
					break;
			}
			return $y;
		}

//		public static function encrypt($string,$key=null) {
//			if(empty($key)){
//				$key = 'WZgfdUps';
//			}
//			$key = str_pad($key, 24, "\0");
//			$cipher_alg = MCRYPT_TRIPLEDES;
//
//			$iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg,MCRYPT_MODE_ECB), MCRYPT_RAND);
//
//
//			$encrypted_string = mcrypt_encrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);
//			return base64_encode($encrypted_string).$key;
//		}
//
//	   public static function decrypt($string,$urldecode = false,$key=null) {
//				if ($urldecode) {
//					$string = urldecode($string);
//				}
//
//				$string = base64_decode($string);
//
//				//key
//				if(empty($key)){
//					$key = 'WZgfdUps';
//				}
//				$key = str_pad($key, 24, "\0");
//
//
//				$cipher_alg = MCRYPT_TRIPLEDES;
//
//				$iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg,MCRYPT_MODE_ECB), MCRYPT_RAND);
//
//
//				$decrypted_string = mcrypt_decrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);
//				return trim($decrypted_string);
//		}
	public static function encryptSupported()
	{
		$cryptoVersion= 0;

		if (function_exists('mcrypt_create_iv') && function_exists('mcrypt_get_iv_size') && function_exists('mcrypt_encrypt') && function_exists('mcrypt_decrypt'))
		{
			$cryptoVersion= 1;
		}
		if (function_exists('openssl_random_pseudo_bytes') && function_exists('openssl_cipher_iv_length') && function_exists('openssl_encrypt') && function_exists('openssl_decrypt'))
		{
			$cryptoVersion= 2;
		}

		return $cryptoVersion;
	}

	// OPENSSL
	// - funzione di criptazione/decriptazione basato su una chiave
	public static function encryptOpenSll($string,$key=null,$usebase64=true) {
		$cipher = 'AES-256-CBC';
		// Must be exact 32 chars (256 bit)
		$password = substr(hash('sha256', $key, true), 0, 32);
		// IV must be exact 16 chars (128 bit)
		$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
		$encrypted_string = openssl_encrypt($string, $cipher, $password, OPENSSL_RAW_DATA, $iv);

//		return base64_encode($encrypted_string);
		if ($usebase64) {
			return base64_encode($encrypted_string);
		}

		return self::strtohex($encrypted_string);
	}
	public static function decryptOpenSll($string,$urldecode = false,$key=null,$usebase64=true) {
		if ($urldecode) {
			$string = urldecode($string);
		}
		if ($usebase64) {
			$string = base64_decode($string);
		}else{
			$string = self::hextostr($string);
		}


		$cipher = 'AES-256-CBC';
		// Must be exact 32 chars (256 bit)
		$password = substr(hash('sha256', $key, true), 0, 32);
		// IV must be exact 16 chars (128 bit)
		$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
		$encrypted_string = openssl_decrypt($string, $cipher, $password, OPENSSL_RAW_DATA, $iv);
		return $encrypted_string;
	}

	// MCRYPT
	// - funzione di criptazione/decriptazione basato su una chiave
	public static function encryptMcrypt($string,$key=null,$usebase64=true) {
		//Key
		if(empty($key)){
			$key = COM_BOOKINGFORCONNECTOR_KEY;
		}

		$key = str_pad($key, 24, "\0");

		//Encryption
		$cipher_alg = MCRYPT_TRIPLEDES;

		$iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg,MCRYPT_MODE_ECB), MCRYPT_RAND);


		$encrypted_string = mcrypt_encrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);
//		return base64_encode($encrypted_string).$key;
		if ($usebase64) {
			return base64_encode($encrypted_string).$key;
		}
		return self::strtohex($encrypted_string);
	}

   public static function decryptMcrypt($string,$urldecode = false,$key=null,$usebase64=true) {
			if ($urldecode) {
				$string = urldecode($string);
			}
			if ($usebase64) {
				$string = base64_decode($string);
			}

			//key
			if(empty($key)){
				$key = COM_BOOKINGFORCONNECTOR_KEY;
			}
			$key = str_pad($key, 24, "\0");

			$cipher_alg = MCRYPT_TRIPLEDES;

			$iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher_alg,MCRYPT_MODE_ECB), MCRYPT_RAND);

			$decrypted_string = mcrypt_decrypt($cipher_alg, $key, $string, MCRYPT_MODE_ECB, $iv);
			return trim($decrypted_string);
	}


	public static function encrypt($string,$key=null,$usebase64=true) {
		if (COM_BOOKINGFORCONNECTOR_CRYPTOVERSION==1) {
			return self::encryptMcrypt($string,null,$usebase64);
		}
		if (COM_BOOKINGFORCONNECTOR_CRYPTOVERSION==2) {
			return self::encryptOpenSll($string,$key,$usebase64);
		}
		return null;
	}
	public static function decrypt($string,$urldecode = false,$key=null,$usebase64=true) {
		if (COM_BOOKINGFORCONNECTOR_CRYPTOVERSION==1) {
			return self::decryptMcrypt($string,$urldecode,$usebase64);
		}
		if (COM_BOOKINGFORCONNECTOR_CRYPTOVERSION==2) {
			return self::decryptOpenSll($string,$urldecode,$key,$usebase64);
		}
		return null;
	}

    public static function strtohex($x)
    {
        $s='';
        foreach (str_split($x) as $c) $s.=sprintf("%02X",ord($c));
        return($s);
    }
	public static function hextostr($hex)
	{
		$string = '';
		for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
			$string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
		}
		return $string;
	}

		public static function getOrderMerchantPaymentId($order) {
			if(!empty($order)){
				$bookingTypeId = self::getItem($order->NotesData, 'bookingTypeId');
				if ($bookingTypeId!=''){
					return $bookingTypeId;
				}
				$bookingTypeId = self::getItem($order->NotesData, 'merchantBookingTypeId');
				if ($bookingTypeId!=''){
					return $bookingTypeId;
				}
			}
			return null;
		}

		public static function calculate_paxages($post, $adults = NULL, $children = NULL, $seniores = NULL) {
			$seniores = isset($seniores) ? $seniores : 0;
			$adults = isset($adults) ? $adults : BFCHelper::$defaultAdultsQt;
			$children = isset($children) ? $children : 0;
			$strAges = array();
			for ($i = 0; $i < $adults; $i++) {
			  $strAges[] = BFCHelper::$defaultAdultsAge;
			}
			for ($i = 0; $i < $seniores; $i++) {
			  $strAges[] = BFCHelper::$defaultSenioresAge;
			}
			if ($children > 0) {
			  for ($i = 0;$i < $children; $i++) {
				$age = $post['childages'.($i+1)];
				if($age == NULL) {
				  $age = 0;
				}
				 if ($age < BFCHelper::$defaultAdultsAge) {
				   $strAges[] = $age;
				 }
			  }
			}
			return $strAges;
		}
		public static function getCustomerData($formData) {
			if ($formData == null) {
				$formData = $_POST['form'];
			}

					$AdditionalQuestionValues = [];

			$Language = isset($formData['Language'])?$formData['Language']:''; 
					$Firstname = isset($formData['Name'])?$formData['Name']:''; //
					$Lastname = isset($formData['Surname'])?$formData['Surname']:''; // => $formData['Surname'],
					$Email = isset($formData['Email'])?$formData['Email']:''; // => $formData['Email'],
			$gender = isset($formData['Gender'])?self::getOptionsFromSelect($formData, 'Gender'):'';
			$Phone = isset($formData['Phone'])?$formData['Phone']:''; // => $formData['Phone'],
			$VatCode = isset($formData['VatCode'])?$formData['VatCode']:''; // => $formData['VatCode'],
			$Organization = isset($formData['Organization'])?$formData['Organization']:''; // => $formData['Organization'],
					$Address = isset($formData['Address'])?$formData['Address']:''; // => $formData['Address'],
					$Zip = isset($formData['Cap'])?$formData['Cap']:''; // => $formData['Cap'],
					$City = isset($formData['City'])?$formData['City']:''; // => $formData['City'],
					$Country = isset($formData['Provincia'])?$formData['Provincia']:''; // => $formData['Provincia'],
					$Nation = isset($formData['Nation'])?self::getOptionsFromSelect($formData, 'Nation'):''; // => self::getOptionsFromSelect($formData, 'Nation'),
					$Fax = isset($formData['Fax'])?$formData['Fax']:''; // => $formData['Fax'],
			$UserCulture = isset($formData['CultureCode'])?self::getOptionsFromSelect($formData, 'CultureCode'):''; // => self::getOptionsFromSelect($formData, 'Culture'),
					$questions =  (isset($formData['Question'])?$formData['Question']:null); 
					$experience =  (isset($formData['Experience'])?$formData['Experience']:null); 
			$BirthDate = isset($formData['BirthDate']) ? DateTime::createFromFormat("Y-m-d", $formData['BirthDate'],new DateTimeZone('UTC')) : null; // => $formData['BirthDate'],
			$BirthLocation = isset($formData['BirthLocation'])?$formData['BirthLocation']:''; // => $formData['BirthLocation'],
			$PassportId = isset($formData['PassportId'])?$formData['PassportId']:''; // => $formData['PassportId'],
			$PassportExpiration = isset($formData['PassportExpiration']) ? DateTime::createFromFormat("Y-m-d", $formData['PassportExpiration'],new DateTimeZone('UTC')) : null; // => $formData['PassportExpiration'],
			$DocumentType = isset($formData['DocumentType'])?intval($formData['DocumentType']):0; // => $formData['DocumentType'],
			$DocumentNumber = isset($formData['DocumentNumber'])?$formData['DocumentNumber']:''; // => $formData['DocumentNumber'],
			$DocumentRelease = isset($formData['DocumentRelease'])?$formData['DocumentRelease']:''; // => $formData['DocumentRelease'],
			$DocumentReleaseDate = isset($formData['DocumentReleaseDate']) ? DateTime::createFromFormat("Y-m-d", $formData['DocumentReleaseDate'],new DateTimeZone('UTC')) :null; // => $formData['DocumentReleaseDate'],
			$DocumentDate = isset($formData['DocumentDate']) ? DateTime::createFromFormat("Y-m-d", $formData['DocumentDate'],new DateTimeZone('UTC')) :null; // => $formData['DocumentDate'],

			$customerData = array(
					'Firstname' => $Firstname,
					'Lastname' => $Lastname,
					'Email' => $Email,
				'Gender' => $gender,
					'Address' => $Address,
					'Zip' => $Zip,
					'City' => $City,
					'Country' => $Country,
					'Nation' => $Nation,
				'Nationality' => $Nation,
					'Phone' => $Phone,
				'Language' => $Language,
				'Organization' => $Organization,
					'Fax' => $Fax,
					'VatCode' => $VatCode,
				'Culture' => $UserCulture,
					'UserCulture' => $UserCulture,
				'BirthDate' => !empty($BirthDate) ? new MyDateTime($BirthDate) : null,
				'BirthLocation' => $BirthLocation,
				'PassportId' => $PassportId,
				'PassportExpiration' => !empty($PassportExpiration) ? new MyDateTime($PassportExpiration) : null,
				'DocumentType' => $DocumentType,
				'DocumentNumber' => $DocumentNumber,
				'DocumentRelease' => $DocumentRelease,
				'DocumentReleaseDate' => !empty($DocumentReleaseDate) ? new MyDateTime($DocumentReleaseDate) : null,
				'DocumentDate' => !empty($DocumentDate) ? new MyDateTime($DocumentDate) : null,
					'AdditionalQuestionValues' => $questions,
				'AdditionalInfosString' => json_encode($experience)
			);
			return $customerData;
		}
		public static function getCrewData($formData) {
			if ($formData == null) {
				$formData = ! empty( $_POST[ 'crew' ] ) ? $_POST['crew'] : null   ;
			}
			$crewData= [];
			if (isset($formData) && !empty($formData)) {
				foreach ( $formData as $index => $resCrews ){
					foreach ( $resCrews as $crew ){
						$AdditionalQuestionValues = new stdClass;

						$Language = isset($crew['Language'])?$crew['Language']:''; 
						$Firstname = isset($crew['Name'])?$crew['Name']:''; //
						$Lastname = isset($crew['Surname'])?$crew['Surname']:''; // => $crew['Surname'],
						$Email = isset($crew['Email'])?$crew['Email']:''; // => $crew['Email'],
						$gender = isset($crew['Gender'])?self::getOptionsFromSelect($crew, 'Gender'):'';
						$Phone = isset($crew['Phone'])?$crew['Phone']:''; // => $crew['Phone'],
						$VatCode = isset($crew['VatCode'])?$crew['VatCode']:''; // => $crew['VatCode'],
						$Organization = isset($crew['Organization'])?$crew['Organization']:''; // => $crew['Organization'],
						$Address = isset($crew['Address'])?$crew['Address']:''; // => $crew['Address'],
						$Zip = isset($crew['Cap'])?$crew['Cap']:''; // => $crew['Cap'],
						$City = isset($crew['City'])?$crew['City']:''; // => $crew['City'],
						$Country = isset($crew['Provincia'])?$crew['Provincia']:''; // => $crew['Provincia'],
						$Nation = isset($crew['Nation'])?self::getOptionsFromSelect($crew, 'Nation'):''; // => self::getOptionsFromSelect($crew, 'Nation'),
						$Fax = isset($crew['Fax'])?$crew['Fax']:''; // => $crew['Fax'],
						$UserCulture = isset($crew['CultureCode'])?self::getOptionsFromSelect($crew, 'CultureCode'):''; // => self::getOptionsFromSelect($crew, 'CultureCode'),
						$questions = (isset($crew['Question'])?$crew['Question']:null); 
						$BirthDate = isset($crew['BirthDate']) ? DateTime::createFromFormat("Y-m-d", $crew['BirthDate'],new DateTimeZone('UTC')) : null; // => $crew['BirthDate'],
						$BirthLocation = isset($crew['BirthLocation'])?$crew['BirthLocation']:''; // => $crew['BirthLocation'],
						$PassportId = isset($crew['PassportId'])?$crew['PassportId']:''; // => $crew['PassportId'],
						$PassportExpiration = isset($crew['PassportExpiration']) ? DateTime::createFromFormat("Y-m-d", $crew['PassportExpiration'],new DateTimeZone('UTC')) : null; // => $crew['PassportExpiration'],
						$DocumentType = isset($crew['DocumentType'])?intval($crew['DocumentType']):0; // => $crew['DocumentType'],
						$DocumentNumber = isset($crew['DocumentNumber'])?$crew['DocumentNumber']:''; // => $crew['DocumentNumber'],
						$DocumentRelease = isset($crew['DocumentRelease'])?$crew['DocumentRelease']:''; // => $crew['DocumentRelease'],
						$DocumentReleaseDate = isset($crew['DocumentReleaseDate']) ? DateTime::createFromFormat("Y-m-d", $crew['DocumentReleaseDate'],new DateTimeZone('UTC')) :null; // => $crew['DocumentReleaseDate'],
						$DocumentDate = isset($crew['DocumentDate']) ? DateTime::createFromFormat("Y-m-d", $crew['DocumentDate'],new DateTimeZone('UTC')) :null; // => $crew['DocumentDate'],
						$questions = isset($crew['Question'])?$crew['Question']:null; 

						$crewData[$index][] = array(
							'Firstname' => $Firstname,
							'Lastname' => $Lastname,
							'Email' => $Email,
							'Gender' => $gender,
							'Address' => $Address,
							'Zip' => $Zip,
							'City' => $City,
							'Country' => $Country,
							'Nation' => $Nation,
							'Nationality' => $Nation,
							'Phone' => $Phone,
							'Language' => $Language,
							'Organization' => $Organization,
							'Fax' => $Fax,
							'VatCode' => $VatCode,
							'Culture' => $UserCulture,
							'UserCulture' => $UserCulture,
							'BirthDate' => !empty($BirthDate) ? new MyDateTime($BirthDate) : null,
							'BirthLocation' => $BirthLocation,
							'PassportId' => $PassportId,
							'PassportExpiration' => !empty($PassportExpiration) ? new MyDateTime($PassportExpiration) : null,
							'DocumentType' => $DocumentType,
							'DocumentNumber' => $DocumentNumber,
							'DocumentRelease' => $DocumentRelease,
							'DocumentReleaseDate' => !empty($DocumentReleaseDate) ? new MyDateTime($DocumentReleaseDate) : null,
							'DocumentDate' => !empty($DocumentDate) ? new MyDateTime($DocumentDate) : null,
							'AdditionalQuestionValues' => $questions
							);
					}
				}
			}
			return $crewData;
		}

		public static function canAcquireCCData($formData) {
			if ($formData == null) {
				$formData = $_POST['form'];
			}

			if(!empty($formData['bookingType'])){
				$bt = $formData['bookingType'];
				if (is_array($bt)) { // if is an array (because it is sent using a select)
					$bt = $bt[0]; // keep only the first value
				}
				if ($bt != '') { // need to check for acquire cc data
					$btData = explode(':',$bt); // data is sent like 'ID:acquireccdata' -> '9:1' or '9:0' or '9:' (where zero is replaced by an empty char)
					if (count($btData) > 1) { // we have more than one value so data sent is correct
						if ($btData[1] != '') { // need to set mandatory for field credit card prefixed with 'cc_' (or other supplied prefix)
							return true;
						}
					}
				}
			}
			return false;
		}

		public static function getCCardData($formData) {
			if ($formData == null) {
				$formData = $_POST['form'];
			}

				if(isset($formData['cc_numero']) && !empty($formData['cc_numero'])) {
					$ccData = array(
							'Type' => self::getOptionsFromSelect($formData,'cc_circuito'),
							'TypeId' => self::getOptionsFromSelect($formData,'cc_circuito'),
							'Number' => $formData['cc_numero'],
							'Name' => $formData['cc_titolare'],
							'ExpiryMonth' => $formData['cc_mese'],
							'ExpiryYear' => $formData['cc_anno']
					);

					return $ccData;
				}
				return null;
		}

		public static function ConvertIntTimeToDate($timeMinEnd)
		{
				$returnDateTime = new DateTime(1, 1, 1);
				if ($timeMinEnd > 0)
				{
					$hour = $timeMinEnd / 10000;
					$minute = ($timeMinEnd - hour * 10000) / 100;
					$returnDateTime->modify('+{$hour} hours');
					$returnDateTime->modify('+{$minute} minutes');
				}
				return $returnDateTime;
		}
		public static function ConvertIntTimeToMinutes($timeMin)
		{
				$returnMinute =0;
				if ($timeMin > 0)
				{
					$hour = $timeMin / 10000;
					$minute = ($timeMin - $hour * 10000) / 100;
					$returnMinute = $hour* 60 + $minute;
				}
				return $returnMinute;
		}

		public static function shorten_string($string, $amount)
		{
			 if(strlen($string) > $amount)
			{
				if ( function_exists( 'mb_substr' ) ){
                     $string = trim(mb_substr($string, 0, $amount)).'...';
                }else{
                    $string = trim(substr($string, 0, $amount))."...";
                }
			}
			return $string;
		}

		public static function getVar($string, $defaultValue=null) {			
//			return isset($_REQUEST[$string]) ?htmlspecialchars($_REQUEST[$string], ENT_QUOTES, 'UTF-8') : $defaultValue;
			$currVal= isset($_REQUEST[$string]) ? $_REQUEST[$string] : $defaultValue;
			if (!is_array($currVal) ) {
			    $currVal = htmlspecialchars($currVal, ENT_QUOTES, 'UTF-8');
			}else{
				foreach ($currVal as $key=>$val  ) {
				    $val=htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
				}
			}
			return $currVal;
		}
		public static function getFloat($string, $defaultValue=null) {

			$jinput = isset($_REQUEST[$string]) ? str_replace(",", ".", $_REQUEST[$string]) : $defaultValue;

			return floatval($jinput);
		}
		public static function getOptionsFromSelect($formData, $str){
			if ($formData == null) {
				$formData = $_POST['form'];
			}

			$aStr = isset($formData[$str])?$formData[$str]:null;
			if(isset($aStr))
			{
				if (!is_array($aStr)) return $aStr;
				$nStr = count($aStr);
				if ($nStr==1){
					return $aStr[0];
				}else
				{
					return implode($aStr, ',');
				}
			}
			return '';
		}

		public static function getSession($string, $defaultValue=null, $prefix ='') {
//			if(empty(COM_BOOKINGFORCONNECTOR_ENABLECACHE)) return null;
			return isset($_SESSION[COM_BOOKINGFORCONNECTOR_SUBSCRIPTION_KEY.$prefix.$string]) ? $_SESSION[COM_BOOKINGFORCONNECTOR_SUBSCRIPTION_KEY.$prefix.$string] : $defaultValue;
		}
		public static function setSession($string, $value=null, $prefix ='') {
			$_SESSION[COM_BOOKINGFORCONNECTOR_SUBSCRIPTION_KEY.$prefix.$string] = $value;
		}

		public static function pushStay($arr, $resourceid, $resStay, $defaultResource = null) {
			$selected = array_values(array_filter($arr, function($itm) use ($resourceid) {
				return $itm->ResourceId == $resourceid;
			}));
			$index = 0;
			if(count($selected) == 0) {
				$obj = new stdClass();
				$obj->ResourceId = $resourceid;

				if(isset($defaultResource) && $defaultResource->ResourceId == $resourceid) {
					$obj->MinCapacityPaxes = $defaultResource->MinCapacityPaxes;
					$obj->MaxCapacityPaxes = $defaultResource->MaxCapacityPaxes;
					$obj->Name = $defaultResource->Name;
					$obj->ImageUrl = $defaultResource->ImageUrl;
					$obj->Availability = $defaultResource->Availability;
					$obj->AvailabilityType = $defaultResource->AvailabilityType;
					$obj->Policy = $resStay->Policy;
				} else {
					$obj->MinCapacityPaxes = $resStay->MinCapacityPaxes;
					$obj->MaxCapacityPaxes = $resStay->MaxCapacityPaxes;
					$obj->Availability = $resStay->Availability;
					$obj->AvailabilityType = $resStay->AvailabilityType;
					$obj->Name = $resStay->ResName;
					$obj->ImageUrl = $resStay->ImageUrl;
					$obj->Policy = $resStay->Policy;
					$obj->TimeLength = $resStay->TimeLength;
				}
				$obj->RatePlans = array();
				//$obj->Policy = $completestay->Policy;
				//$obj->Description = $singleRateplan->Description;
				$arr[] = $obj;
				$index = count($arr) - 1;
			} else {
				$index = array_search($selected[0], $arr);
				//$obj = $selected[0];
			}

			$rt = new stdClass();
			$rt->RatePlanId = $resStay->RatePlanId;
			$rt->Name = $resStay->Name;
			$rt->RatePlanRefId = isset($resStay->RefId) ? $resStay->RefId : "";
			$rt->PercentVariation = $resStay->PercentVariation;

			$rt->TotalPrice=0;
			$rt->TotalPriceString ="";
			$rt->Days=0;
			$rt->BookingType=$resStay->BookingType;
			$rt->IsBookable=$resStay->IsBookable;
			$rt->CheckIn = BFCHelper::parseJsonDate($resStay->CheckIn);
			$rt->CheckOut= BFCHelper::parseJsonDate($resStay->CheckOut);

			$rt->CalculatedPricesDetails = $resStay->CalculatedPricesDetails;
			$rt->SelectablePrices = $resStay->SelectablePrices;
			$rt->Variations = $resStay->Variations;
			$rt->SimpleDiscountIds = implode(',', $resStay->SimpleDiscountIds);
			if(!empty($resStay->SuggestedStay->DiscountedPrice)){
				$rt->TotalPrice = (float)$resStay->SuggestedStay->TotalPrice;
				$rt->TotalPriceString = BFCHelper::priceFormat((float)$resStay->SuggestedStay->TotalPrice);
				$rt->Days = $resStay->SuggestedStay->Days;
				$rt->DiscountedPriceString = BFCHelper::priceFormat((float)$resStay->SuggestedStay->DiscountedPrice);
				$rt->DiscountedPrice = (float)$resStay->SuggestedStay->DiscountedPrice;
			}

			$arr[$index]->RatePlans[] = $rt;

			return $arr;
		}

		public static function ParsePriceParameter($str)
			{
				$array = explode(':',$str);
				$newarray = array(
					"PriceId" => intval($array[0]),
					"ProductId" => intval($array[0]),
					"Quantity" =>intval($array[1]),
					"CheckInDateTime" => count($array) > 2 && !empty($array[2]) ? DateTime::createFromFormat("YmdHis", $array[2],new DateTimeZone('UTC')) : null,
					"PeriodDuration" => count($array) > 3 && !empty($array[3]) ? intval($array[3]) : 0,
					"TimeSlotId" => count($array) > 4 && !empty($array[4]) ? intval($array[4]) : 0,
					"TimeSlotStart" => count($array) > 5 && !empty($array[5]) ? intval($array[5]) : 0,
					"TimeSlotEnd" => count($array) > 6 && !empty($array[6]) ? intval($array[6]) : 0,
					"TimeSlotDate" => count($array) > 7 && !empty($array[7]) ? DateTime::createFromFormat("Ymd", $array[7],new DateTimeZone('UTC')) : null,
					"CheckInDate" => count($array) > 8 && !empty($array[8]) ? DateTime::createFromFormat("Ymd", $array[8],new DateTimeZone('UTC')) : null,
					"CheckOutDate" => count($array) > 9 && !empty($array[9]) ? DateTime::createFromFormat("Ymd", $array[9],new DateTimeZone('UTC')) : null,
					"Configuration" => $str
				);
				return $newarray;
			}

		public static function GetPriceParameters($selectablePrices)
			{
				$priceParameters = array();
				if (empty($selectablePrices)) {
					return $priceParameters;
				}
				$priceParametersArray = explode('|', $selectablePrices);
				if(!empty($priceParametersArray)){
					foreach ($priceParametersArray as $s)
					{
						array_push($priceParameters, BFCHelper::ParsePriceParameter($s));
					}
				}
				return $priceParameters;
			}

			public static function calculateOrder($OrderJson,$language,$bookingType = "") {
				$orderModel = json_decode($OrderJson);
				$order = new StdClass;
				$DateTimeMinValue = new DateTime('UTC');
				$DateTimeMinValue->setDate(1, 1, 1);

				$orderModel->SearchModel->FromDate = DateTime::createFromFormat('d/m/Y', $orderModel->SearchModel->checkin,new DateTimeZone('UTC'));
				$orderModel->SearchModel->ToDate = DateTime::createFromFormat('d/m/Y', $orderModel->SearchModel->checkout,new DateTimeZone('UTC'));
				$orderModel->SearchModel->FromDate->setTime(0,0,0);
				$orderModel->SearchModel->ToDate->setTime(0,0,0);


				if ($orderModel->Resources != null && count($orderModel->Resources) > 0 && $orderModel->SearchModel->FromDate != $DateTimeMinValue)
				{
					$order->Resources = array();
					$resourceDetail = null;

					foreach ($orderModel->Resources as $resource)
					{
						$resourceDetail = BFCHelper::GetResourcesById($resource->ResourceId);
						$order->MerchantId = $resourceDetail->MerchantId;
						$services = "";

						$servicesArray = array_map(function ($i) { return $i->Value; },array_filter($resource->ExtraServices, function($t) use ($resource) {return $t->ResourceId == $resource->ResourceId;}));
						if(!empty($servicesArray)){
							$services = implode("|",$servicesArray);
						}
						$currservices = BFCHelper::GetPriceParameters($services);
						$selectablePrices = array_filter($currservices, function($t) {return $t["Quantity"] > 0;});

						$currModel = clone $orderModel;
						$currModel->SearchModel->MerchantId = $resourceDetail->MerchantId;
						$currModel->SearchModel->ProductAvailabilityType = $resourceDetail->AvailabilityType;
						$duration = 1;

						if ($resourceDetail->AvailabilityType== 2)
						{
							$duration = $resource->TimeDuration;
							$currModel->SearchModel->FromDate = DateTime::createFromFormat("YmdHis", $resource->CheckInTime,new DateTimeZone('UTC'));
							$currModel->SearchModel->ToDate = DateTime::createFromFormat("YmdHis", $resource->CheckInTime,new DateTimeZone('UTC'));

							$currModel->SearchModel->ToDate->modify('+1 day');
						}
						if ($resourceDetail->AvailabilityType== 3)
						{
							$currModel->SearchModel->ToDate = clone $currModel->SearchModel->FromDate;
							$currModel->SearchModel->ToDate->modify('+1 day');
						}
						if($resourceDetail->AvailabilityType != 3 && $resourceDetail->AvailabilityType != 2){
							$duration = $currModel->SearchModel->ToDate->diff($currModel->SearchModel->FromDate)->format('%a');
						}

						if ($resourceDetail->AvailabilityType == 0)
						{
							$duration +=1;
						}

						$paxages = array();
						for ($i=0;$i<$currModel->SearchModel->AdultCount ; $i++)
						{
							array_push($paxages, COM_BOOKINGFORCONNECTOR_ADULTSAGE);
						}
						for ($i=0;$i<$currModel->SearchModel->SeniorCount ; $i++)
						{
							array_push($paxages, COM_BOOKINGFORCONNECTOR_SENIORESAGE);
						}
						$nchsarray = array($currModel->SearchModel->childages1,$currModel->SearchModel->childages2,$currModel->SearchModel->childages3,$currModel->SearchModel->childages4,$currModel->SearchModel->childages5,null);
						for ($i=0;$i<$currModel->SearchModel->ChildrenCount ; $i++)
						{
							array_push($paxages, $nchsarray[$i]);
						}
	//					$paxages = implode("|",$paxages);

						$packages =null;
						$pricetype = !empty($resource->RatePlanId)?$resource->RatePlanId:"";
						$ratePlanId = $pricetype;
						$variationPlanId = "";

						$listRatePlans = BFCHelper::GetCompleteRatePlansStayWP($resource->ResourceId,$currModel->SearchModel->FromDate,$duration,$paxages,$services,$packages,$pricetype,$ratePlanId,$variationPlanId,$language, $bookingType, true);
						if (!empty($listRatePlans) && is_array($listRatePlans)){
							$listRatePlans = array_filter($listRatePlans, function($l)  {return ($l->TotalAmount>0 && !empty($l->SuggestedStay)  && $l->SuggestedStay->Available ) ;});

							if (!empty($resource->RatePlanId))
							{
								$listRatePlans =  array_filter($listRatePlans, function($l) use ($resource) {return $l->RatePlanId == $resource->RatePlanId ;}); // c#: allRatePlans.Where(p => p.ResourceId == resId);
							}
							else
							{
								$listRatePlansGrouped = array();
								$tmpLlistRatePlansGrouped = array();
								foreach ($listRatePlans as $data) {
									$id = $data->SuggestedStay->BookingType;
									if (isset($listRatePlansGrouped[$id])) {
										$listRatePlansGrouped[$id][] = $data;
									} else {
										$listRatePlansGrouped[$id] = array($data);
									}
								}
								foreach ($listRatePlansGrouped as $ratePlansGrouped) {
									usort($ratePlansGrouped, "BFCHelper::bfi_sortRatePlans");
									$tmpLlistRatePlansGrouped[] = reset($ratePlansGrouped);
								}

								$listRatePlans = $tmpLlistRatePlansGrouped;


								$selRatePlan = reset($listRatePlans);

							}
							foreach ($listRatePlans as $selRatePlan)
							{

								if (!empty($selRatePlan))
								{

									//$order->BookingType = $selRatePlan->SuggestedStay->BookingType;
									for ($i = 0; $i < $resource->SelectedQt; $i++)
									{
										$lstExtraServices = array();
										$lstPriceSimpleResult = json_decode($selRatePlan->CalculatedPricesString);
										$lstPriceSimpleResult = array_filter($lstPriceSimpleResult, function($c) use ($resource) {return $c->RelatedProductId != $resource->ResourceId ;});
										$lstPriceSimpleResultGrouped = array();
										foreach ($lstPriceSimpleResult as $data) {
										  $id = $data->RelatedProductId;
										  if (isset($lstPriceSimpleResultGrouped[$id])) {
											 $lstPriceSimpleResultGrouped[$id][] = $data;
										  } else {
											 $lstPriceSimpleResultGrouped[$id] = array($data);
										  }
										}
										foreach ($lstPriceSimpleResultGrouped as $pricesKey => $prices ){
											$resInfo = reset($prices);

											$resInfoRequest = current(array_filter($selectablePrices, function($c) use ($pricesKey) {return $c["ProductId"] == $pricesKey;}));

											$CalculatedQt = 0;
											$TotalAmount = 0;
											$TotalDiscounted = 0;
											foreach ($prices as $item) {
												$CalculatedQt += $item->CalculatedQt;
												$TotalAmount += $item->TotalAmount;
												$TotalDiscounted += $item->TotalDiscounted;
											}
											$currSelectedService = new StdClass;
											$currSelectedService->PriceId = $pricesKey;
											$currSelectedService->CalculatedQt = $CalculatedQt;
											$currSelectedService->ResourceId = $pricesKey;
											$currSelectedService->Name = $resInfo->Name;
											$currSelectedService->TotalAmount = $TotalAmount;
											$currSelectedService->TotalDiscounted = $TotalDiscounted;
											$currSelectedService->TimeSlotDate = !empty($resInfoRequest["TimeSlotDate"]) ? $resInfoRequest["TimeSlotDate"]->format('d/m/Y') : ""; //$resInfoRequest["TimeSlotDate"];
											$currSelectedService->TimeSlotStart = $resInfoRequest["TimeSlotStart"];
											$currSelectedService->TimeSlotEnd = $resInfoRequest["TimeSlotEnd"];
											$currSelectedService->TimeSlotId = $resInfoRequest["TimeSlotId"];
											$currSelectedService->CheckInTime = !empty($resInfoRequest["CheckInDateTime"]) ? $resInfoRequest["CheckInDateTime"]->format('YmdHis') : "";
											$currSelectedService->TimeDuration = !empty($resInfoRequest["PeriodDuration"]) ? $resInfoRequest["PeriodDuration"] : "";

											array_push($lstExtraServices, $currSelectedService);
										}

										$calPricesResources = json_decode($selRatePlan->CalculatedPricesString);
										$calPricesResources = array_filter($calPricesResources, function($c) use ($resource) {return $c->RelatedProductId == $resource->ResourceId ;});

	//									$calPricesResources = array_filter($calPricesResources, function($c) {
	//										return $c->Tag == "person" || $c->Tag == "default" || $c->Tag == "" || $c->Tag== "timeslot" || $c->Tag == "timeperiod" ;
	//									});

										$calPricesResourcesTotalAmount = 0;
										$calPricesResourcesTotalDiscounted = 0;
										foreach ($calPricesResources as $item) {
											$calPricesResourcesTotalAmount += $item->TotalAmount;
											$calPricesResourcesTotalDiscounted += $item->TotalDiscounted;
										}
										$AllVariations = "";

										if(!empty($selRatePlan->AllVariationsString)){
											$allVariationPlanId = array_unique(array_map(function ($i) { return $i->VariationPlanId; }, json_decode($selRatePlan->AllVariationsString)));
											$AllVariations = implode(",",$allVariationPlanId);

										}


										$SelectedResource = new StdClass;
										$SelectedResource->ResourceId = $resource->ResourceId;
										$SelectedResource->MerchantId = $resource->MerchantId;
										$SelectedResource->RatePlanId = $resource->RatePlanId;
										$SelectedResource->SelectedQt = $resource->SelectedQt;
										$SelectedResource->TimeSlotId = isset($resource->TimeSlotId)?$resource->TimeSlotId:null;
										$SelectedResource->TimeSlotStart = isset($resource->TimeSlotStart)?$resource->TimeSlotStart:null;
										$SelectedResource->TimeSlotEnd = isset($resource->TimeSlotEnd)?$resource->TimeSlotEnd:null;
										$SelectedResource->CheckInTime = (isset($selRatePlan->SuggestedStay->CheckIn) )?BFCHelper::parseJsonDate($selRatePlan->SuggestedStay->CheckIn,'YmdHis'):$currModel->SearchModel->FromDate->format('YmdHis');
										$SelectedResource->TimeDuration = isset($resource->TimeDuration)?$resource->TimeDuration:null;
										$SelectedResource->Name = $resourceDetail->Name;
										$SelectedResource->BookingType = $selRatePlan->SuggestedStay->BookingType;
										$SelectedResource->AvailabilityType = $resourceDetail->AvailabilityType;
										$SelectedResource->TotalAmount = $calPricesResourcesTotalAmount;
										$SelectedResource->TotalDiscounted = $calPricesResourcesTotalDiscounted;
										$SelectedResource->ExtraServices = $lstExtraServices;
										$SelectedResource->ExtraServicesValue = $services;
										$SelectedResource->RatePlanName = $selRatePlan->Name;
										$SelectedResource->PercentVariation = $selRatePlan->PercentVariation;
										$SelectedResource->AllVariations = $AllVariations;
										$SelectedResource->PolicyId = 0;
										if(isset($selRatePlan->Policy) && !empty($selRatePlan->Policy->PolicyId) ){
											$SelectedResource->PolicyId = $selRatePlan->Policy->PolicyId;
										}
										array_push($order->Resources, $SelectedResource);

									}

								}
							}
						}
						$order->TotalAmount = 0;
						$order->TotalDiscountedAmount = 0;
						foreach ($order->Resources as $resource)
						{
							$order->TotalAmount += $resource->TotalAmount;
							$order->TotalDiscountedAmount += $resource->TotalDiscounted ;
							foreach ($resource->ExtraServices as $item) {
								$order->TotalAmount  += $item->TotalAmount;
								$order->TotalDiscountedAmount  += $item->TotalDiscounted;
							}

						}
						$order->SearchModel = $orderModel->SearchModel;

				}
				}

				return $order;
		}
			public static function CreateOrder($OrderJson,$language,$bookingType = "") {
	//			$totalModel = json_decode(stripslashes($OrderJson));


				$orderModel = json_decode(stripslashes($OrderJson));
				$lstOrderStay = array();
				$DateTimeMinValue = new DateTime('UTC');
				$DateTimeMinValue->setDate(1, 1, 1);

	//            foreach ($totalModel as $orderModel)
	//            {

	//			if(isset($orderModel->SearchModel->checkin)){
	//				$orderModel->SearchModel->FromDate = DateTime::createFromFormat('d/m/Y', $orderModel->SearchModel->checkin);
	//			}else{
	//				$orderModel->SearchModel->FromDate = new DateTime($orderModel->SearchModel->FromDate );
	//			}
	//			if(isset($orderModel->SearchModel->checkout)){
	//				$orderModel->SearchModel->ToDate = DateTime::createFromFormat('d/m/Y', $orderModel->SearchModel->checkout);
	//			}else{
	//				$orderModel->SearchModel->ToDate = new DateTime($orderModel->SearchModel->ToDate );
	//			}
	//
	//			$orderModel->SearchModel->FromDate->setTime(0,0,0);
	//			$orderModel->SearchModel->ToDate->setTime(0,0,0);

	//				echo "<pre>";
	//				echo print_r($orderModel->SearchModel);
	//				echo "</pre>";
	//				die();

	//            if ($orderModel->Resources != null && count($orderModel->Resources) > 0 && $orderModel->SearchModel->FromDate != $DateTimeMinValue)
				if ($orderModel->Resources != null && count($orderModel->Resources) > 0 )
				{
	//                $resourceDetail = null;
					foreach ($orderModel->Resources as $resource)
					{
	//                    $resourceDetail = BFCHelper::GetResourcesById($resource->ResourceId);
	//                    $order->MerchantId = $resourceDetail->MerchantId;

						$fromCart= !empty($resource->CartOrderId)?1:0;
						$services="";
						if(isset($resource->ExtraServicesValue)){
							$services = $resource->ExtraServicesValue;
						}
						if(isset($resource->ExtraServices)){
							$servicesArray = array_map(function ($i) { return $i->Value; },array_filter($resource->ExtraServices, function($t) use ($resource) {return $t->ResourceId == $resource->ResourceId;}));
							if(!empty($servicesArray)){
								$services = implode("|",$servicesArray);
							}
						}

	//					$services = isset($resource->ExtraServicesValue)?$resource->ExtraServicesValue:(isset($resource->ExtraServices)?json_encode($resource->ExtraServices):"");

	//					$servicesArray = array_map(function ($i) { return $i->Value; },array_filter($resource->ExtraServices, function($t) use ($resource) {return $t->ResourceId == $resource->ResourceId;}));
	//					if(!empty($servicesArray)){
	//						$services = implode("|",$servicesArray);
	//					}
	//					$currservices = BFCHelper::GetPriceParameters($services);
	//                    $selectablePrices = array_filter($currservices, function($t) {return $t["Quantity"] > 0;});

	//					$currModel = clone $orderModel;
						$currModel = new stdClass;
						$currModel->SearchModel = new stdClass;
						if($fromCart==0){
							$currModel->SearchModel->FromDate  = DateTime::createFromFormat('d/m/Y\TH:i:s', $resource->FromDate,new DateTimeZone('UTC'));
							$currModel->SearchModel->ToDate  = DateTime::createFromFormat('d/m/Y\TH:i:s', $resource->ToDate,new DateTimeZone('UTC'));
						}else{
							$currModel->SearchModel->FromDate = new DateTime($resource->FromDate,new DateTimeZone('UTC') );
							$currModel->SearchModel->ToDate = new DateTime($resource->ToDate,new DateTimeZone('UTC') );
						}
						$currModel->SearchModel->FromDate->setTime(0,0,0);
						$currModel->SearchModel->ToDate->setTime(0,0,0);
						$currModel->SearchModel->MerchantId = $resource->MerchantId;
						$currModel->SearchModel->ProductAvailabilityType = $resource->AvailabilityType;
						$duration = 1;

						if ($resource->AvailabilityType== 2)
						{
							$duration = $resource->TimeDuration;
							$currModel->SearchModel->FromDate = DateTime::createFromFormat("YmdHis", $resource->CheckInTime,new DateTimeZone('UTC'));
							$currModel->SearchModel->ToDate = DateTime::createFromFormat("YmdHis", $resource->CheckInTime,new DateTimeZone('UTC'));
							$currModel->SearchModel->ToDate->modify('+1 day');
						}
						if ($resource->AvailabilityType== 3)
						{
							$currModel->SearchModel->ToDate = clone $currModel->SearchModel->FromDate;
							$currModel->SearchModel->ToDate->modify('+1 day');
						}
						if($resource->AvailabilityType != 3 && $resource->AvailabilityType != 2){
							$duration = $currModel->SearchModel->ToDate->diff($currModel->SearchModel->FromDate)->format('%a');
						}
						$paxages = $resource->PaxAges;
						if ($duration ==0 && $resource->AvailabilityType ==0) {
						    $duration = 1;
						}

	//					$paxages = array();
	//					for ($i=0;$i<$currModel->SearchModel->AdultCount ; $i++)
	//					{
	//						array_push($paxages, COM_BOOKINGFORCONNECTOR_ADULTSAGE);
	//					}
	//					for ($i=0;$i<$currModel->SearchModel->SeniorCount ; $i++)
	//					{
	//						array_push($paxages, COM_BOOKINGFORCONNECTOR_SENIORESAGE);
	//					}
	////					for ($i=0;$i<$currModel->SearchModel->ChildrenCount ; $i++)
	//////					$paxages = implode("|",$paxages);
	//					$nchsarray = array($currModel->SearchModel->childages1,$currModel->SearchModel->childages2,$currModel->SearchModel->childages3,$currModel->SearchModel->childages4,$currModel->SearchModel->childages5,null);
	//					for ($i=0;$i<$currModel->SearchModel->ChildrenCount ; $i++)
	//					{
	//						array_push($paxages, $nchsarray[$i]);
	//					}

						$packages =null;
						$pricetype = !empty($resource->RatePlanId)?$resource->RatePlanId:"";
						$ratePlanId = $pricetype;
						$variationPlanId = "";


						$stay = BFCHelper::GetCompleteRatePlansStayWP($resource->ResourceId,$currModel->SearchModel->FromDate,$duration,$paxages,$services,$packages,$pricetype,$ratePlanId,$variationPlanId,$language, $bookingType , false);
						if (!empty($stay) && is_array($stay)){
							$stay = reset($stay);
						}
						if (!empty($stay) && !empty($stay->SuggestedStay))
						{
							for ($i = 0; $i < $resource->SelectedQt; $i++)
							{
								$order = new StdClass;

								$order->Availability = $stay->SuggestedStay->Availability;
								$order->Available = $stay->SuggestedStay->Available;
								$order->MerchantBookingTypeId = intVal($bookingType);
								$order->CheckIn = $stay->SuggestedStay->CheckIn;
								$order->CheckOut = $stay->SuggestedStay->CheckOut;
								$order->Days = $stay->SuggestedStay->Days;
								$order->DiscountDescription = $stay->SuggestedStay->DiscountDescription;
								$order->DiscountId = $stay->SuggestedStay->DiscountId;
								$order->MerchantId = $resource->MerchantId;
								$order->Extras = $stay->SuggestedStay->Extras;
								$order->ExtrasDiscount = $stay->SuggestedStay->ExtrasDiscount;
								$order->HolidayDiscount = $stay->SuggestedStay->HolidayDiscount;
								$order->HolidayPrice = $stay->SuggestedStay->HolidayPrice;
								$order->IsOffer = $stay->SuggestedStay->IsOffer;
								$order->Paxes = $stay->SuggestedStay->Paxes;
								$order->PaxesDiscount = $stay->SuggestedStay->PaxesDiscount;
								$order->PaxesPrice = $stay->SuggestedStay->PaxesPrice;
								$order->TotalDiscount = $stay->SuggestedStay->TotalDiscount;
								$order->TotalPrice = $stay->SuggestedStay->TotalPrice;
								$order->UnitId = $stay->SuggestedStay->UnitId;
								$order->DiscountedPrice = $stay->SuggestedStay->DiscountedPrice;
								$order->RatePlanStay = $stay;
								$order->CalculatedPricesDetails = json_decode($stay->CalculatedPricesString);
								//$order->SelectablePrices = json_decode($stay->CalculablePricesString);
								//$order->CalculatedPackages = json_decode($stay->PackagesString);
								//$order->MerchantBookingTypesString = json_decode($stay->MerchantBookingTypesString);
								$order->Variations = json_decode($stay->AllVariationsString);
								$order->DiscountVariation = !empty($stay->Discount) ? $stay->Discount : null;
								$order->SupplementVariation = !empty($stay->Supplement) ? $stay->Supplement : null;
								$order->TimeSlotId =  isset($resource->TimeSlotId)?$resource->TimeSlotId:"";
								$order->TimeSlotStart = isset($resource->TimeSlotStart)?$resource->TimeSlotStart:"";
								$order->TimeSlotEnd = isset($resource->TimeSlotEnd)?$resource->TimeSlotEnd:"";
								$order->CheckInTime = isset($resource->CheckInTime)?$resource->CheckInTime:"";
								$order->TimeDuration = isset($resource->TimeDuration)?$resource->TimeDuration:"";
								$order->ServiceConfiguration = $services;
								$order->PolicyId = 0;

								if(isset($stay->Policy) && !empty($stay->Policy->PolicyId)) {
									$order->PolicyId = $stay->Policy->PolicyId;
								}
								if(isset($stay->Policy) && !empty($stay->Policy->PolicyId)) {
									$order->PolicyId = $stay->Policy->PolicyId;
								}

								unset($order->RatePlanStay->CalculatedPricesString);
								unset($order->RatePlanStay->CalculablePricesString);
								unset($order->RatePlanStay->PackagesString);
								unset($order->RatePlanStay->MerchantBookingTypesString);
								unset($order->RatePlanStay->Policy);
								unset($order->RatePlanStay->SuggestedStay);
								unset($order->RatePlanStay->AllVariationsString);

								foreach($order->CalculatedPricesDetails as $pr) {
									unset($pr->OriginalDays);
									unset($pr->Days);
									unset($pr->Variations);
								}
	//							if($fromCart==0){
	//								$order->CheckIn = DateTime::createFromFormat('d/m/Y\TH:i:s', $resource->FromDate);
	//								$order->CheckOut = DateTime::createFromFormat('d/m/Y\TH:i:s', $resource->ToDate);
	//							}

								array_push($lstOrderStay, $order);
							}
						}
					}

				}
	//		}

				return $lstOrderStay;
		}

		public static function AddToCart($tmpUserId, $language, $OrderJson, $ResetCart) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//JML>		$model = JModelLegacy::getInstance('Orders', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOrders;
			return $model->AddToCart($tmpUserId, $language, $OrderJson, $ResetCart);
		}
		public static function AddToCartSimple($tmpUserId, $language, $OrderJson, $ResetCart) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//JML>		$model = JModelLegacy::getInstance('Orders', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOrders;
			return $model->AddToCartSimple($tmpUserId, $language, $OrderJson, $ResetCart);
		}

		public static function AddToCartByExternalUser($tmpUserId, $language, $OrderJson, $ResetCart) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//JML>		$model = JModelLegacy::getInstance('Orders', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOrders;
			return $model->AddToCartByExternalUser($tmpUserId, $language, $OrderJson, $ResetCart);
		}
		public static function DeleteFromCartByExternalUser($tmpUserId, $language, $CartOrderId) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//JML>		$model = JModelLegacy::getInstance('Orders', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOrders;
			return $model->DeleteFromCartByExternalUser($tmpUserId, $language, $CartOrderId);
		}
		public static function AddDiscountCodesCartByExternalUser($tmpUserId, $language, $bficoupons) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//JML>		$model = JModelLegacy::getInstance('Orders', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOrders;
			return $model->AddDiscountCodesCartByExternalUser($tmpUserId, $language, $bficoupons);
		}
		public static function GetCartByExternalUser($tmpUserId, $language, $includeDetails = true) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
	//JML>		$model = JModelLegacy::getInstance('Orders', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOrders;
			return $model->GetCartByExternalUser($tmpUserId, $language, $includeDetails);
		}
		public static function GetCartByExternalUserSimple($tmpUserId, $language, $includeDetails = true) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOrders;
	//JML>		$model = JModelLegacy::getInstance('Orders', 'BookingForConnectorModel');
			return $model->GetCartByExternalUserSimple($tmpUserId, $language, $includeDetails);
		}
		public static function UpdateCartExternalUser($oldUserId) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelOrders;
	//JML>		$model = JModelLegacy::getInstance('Orders', 'BookingForConnectorModel');
			return $model->UpdateCartExternalUser($oldUserId);
		}

		public static function GetContactFavoriteGroups() {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//JML>		$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPortal;
			return $model->GetContactFavoriteGroups();
		}

		public static function AddToFavorites($itemId, $itemType, $itemName, $itemUrl, $groupId = 0, $startDate = "", $endDate = "") {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//JML>		$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPortal;
			return $model->AddToFavorites($itemId, $itemType, $itemName, $itemUrl, $groupId, $startDate, $endDate);
		}
		public static function RemoveItemToFavorites($favoriteId, $groupId = 0) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//JML>		$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPortal;
			return $model->RemoveItemToFavorites($favoriteId, $groupId );
		}
		public static function AddFavoriteGroup($groupName) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//JML>		$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPortal;
			return $model->AddFavoriteGroup($groupName);
		}


		public static function GetPointsOfInterest() {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//JML>		$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPortal;
			return $model->GetPointsOfInterest();
		}
		public static function GetPointOfInterestCategories() {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//JML>		$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPointsofinterests;
			return $model->getCategories();
		}

		public static function GetProximityPoi($points) {
//			JModelLegacy::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR . 'com_bookingforconnector'. DIRECTORY_SEPARATOR . 'models', 'BookingForConnectorModel');
//JML>		$model = JModelLegacy::getInstance('Portal', 'BookingForConnectorModel');
			$model = new BookingForConnectorModelPointsofinterests;
			return $model->getProximityPoi($points);
		}


		public static function bfi_get_userId() {

			$currUser = BFCHelper::getSession('bfiUser',null, 'bfi-User');
			if ($currUser!=null && !empty($currUser->Email)) {
				return $currUser->Email;
			}

			$tmpUserId = BFCHelper::getSession('tmpUserId', null , 'com_bookingforconnector');
			if(empty($tmpUserId)){
//				$uid = get_current_user_id();
//				$user = get_user_by('id', $uid);
//				if (!empty($user->ID)) {
//					$tmpUserId = $user->ID."|". $user->user_login . "|" . $_SERVER["SERVER_NAME"];
//				}
				if(empty($tmpUserId)){
					$tmpUserId = uniqid($_SERVER["SERVER_NAME"]);
				}
				BFCHelper::setSession('tmpUserId', $tmpUserId , 'com_bookingforconnector');
			}

			return $tmpUserId;
		}

		public static function bfi_sortOrder($a, $b)
		{
			return $a->SortOrder - $b->SortOrder;
		}
		public static function bfi_sortResourcesRatePlans($a, $b)
		{
			return $a->RatePlan->TotalDiscounted - $b->RatePlan->TotalDiscounted;
	//		return $a->RatePlan->SortOrder - $b->RatePlan->SortOrder;
		}
		public static function bfi_sortMultiRatePlans($a, $b)
		{
			if ($a->RatePlan->SuggestedStay->TotalPaxes < $b->RatePlan->SuggestedStay->TotalPaxes ) return 1;
			if ($a->RatePlan->SuggestedStay->TotalPaxes  > $b->RatePlan->SuggestedStay->TotalPaxes ) return -1;
			if ($a->RatePlan->IncludedMeals < $b->RatePlan->IncludedMeals)  return 1;
			if ($a->RatePlan->IncludedMeals > $b->RatePlan->IncludedMeals)  return -1;
			if ($a->RatePlan->TotalDiscounted < $b->RatePlan->TotalDiscounted)  return 1;
			if ($a->RatePlan->TotalDiscounted > $b->RatePlan->TotalDiscounted)  return -1;
			return 0;

	//		return $a->RatePlan->SortOrder - $b->RatePlan->SortOrder;
		}
//		public static function bfi_sortMultiRatePlansByComputedPaxes($a, $b)
//		{
//			return  ($a->RatePlan->SuggestedStay->ComputedPaxesParameters[0]->Quantity < $b->RatePlan->SuggestedStay->ComputedPaxesParameters[0]->Quantity ) ? 1 : -1;
////			return  ($a->RatePlan->SuggestedStay->TotalComputedPaxes < $b->RatePlan->SuggestedStay->TotalComputedPaxes ) ? 1 : -1;
//
//		}
		public static function bfi_returnFilterCount($a, $b, $offset)
		{
			$currA = intval($a);
			$currB = $currA;
			if(isset($b[$offset])){
				$currB = intval($b[$offset]);
			}
	//		if($currA>$currB){
	//			return  "+" . ($currA - $currB);
	//		}

			return $currB;
		}

		public static function buildAddress($Address) {
			$indirizzo = isset($Address->Address)?$Address->Address:"";
			$cap = isset($Address->ZipCode)?$Address->ZipCode:""; 
			$comune = isset($Address->CityName)?$Address->CityName:"";
			$stato = isset($Address->StateName)?$Address->StateName:"";
			$strAddress = "";
			if (!empty($indirizzo)) {
				$strAddress = '<span class="street-address">' . $indirizzo .'</span>';
			}
			if (!empty($cap) || !empty($comune)) {
				if(!empty( $strAddress )){
					$strAddress .=", ";
				}
				$strAddress .= '<span class="postal-code">' . $cap .'</span> ';
				$strAddress .= '<span class="locality">' . $comune .'</span>';
			}
			if (!empty($stato)) {
				if(!empty( $strAddress )){
					$strAddress .=", ";
				}
				$strAddress .= '<span class="region">' . $stato .'</span>';
			}
			return $strAddress;
		}

		public static function string_sanitize($s) {
			$result = preg_replace("/[^a-zA-Z0-9\s]+/", "", html_entity_decode($s, ENT_QUOTES));
			return $result;
		}
		public static function escapeJavaScriptText($string)
		{
			return str_replace("\n", '\n', str_replace('"', '\"', addcslashes(str_replace("\r", '', (string)$string), "\0..\37'\\")));
		}
		public static function bfi_get_clientdata() {
			$ipClient = BFCHelper::bfi_get_client_ip();
			$ipServer = $_SERVER['SERVER_ADDR'];
			$uaClient = $_SERVER['HTTP_USER_AGENT'];
			$RequestTime = $_SERVER['REQUEST_TIME'];
			$Referer = $_SERVER['HTTP_REFERER'];
			$clientdata =
				"ipClient:" . str_replace( ":", "_", $ipClient) ."|".
				"ipServer:" . str_replace( ":", "_", $ipServer) ."|".
				"uaClient:" . str_replace( "|", "_", str_replace( ":", "_", $uaClient)) ."|".
				"Referer:" . str_replace( "|", "_", str_replace( ":", "_", $Referer)) ."|".
				"RequestTime:" . $RequestTime;
			return $clientdata;
		}
		public static function bfi_get_client_ip() {
			$ipaddress = '';
			if (isset($_SERVER['HTTP_CLIENT_IP']))
				$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else if(isset($_SERVER['HTTP_X_FORWARDED']))
				$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
				$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			else if(isset($_SERVER['HTTP_FORWARDED']))
				$ipaddress = $_SERVER['HTTP_FORWARDED'];
			else if(isset($_SERVER['REMOTE_ADDR']))
				$ipaddress = $_SERVER['REMOTE_ADDR'];
			else
				$ipaddress = 'UNKNOWN';

			return $ipaddress;
		}
		public static function bfi_get_curr_url() {
			return (isset($_SERVER['HTTPS']) ? "https" : "http") . ':' ."//" .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}

		public static function bfi_utf8ize($d) {
			if (is_array($d))
				foreach ($d as $k => $v)
					$d[$k] = self::bfi_utf8ize($v);
			 else if(is_object($d))
				foreach ($d as $k => $v)
					$d->$k = self::bfi_utf8ize($v);
			 else
				return utf8_encode($d);
			return $d;
		}

//----------------------------------
//	openstreemap functions
//----------------------------------
		public static function bfi_getCoordOffset_openstreetmap($what, $lat, $lon, $offset) {
			$earthRadius = 6378137;
			$coord = [0 => $lat, 1 => $lon];

			$radOff = $what === 0 ? $offset / $earthRadius : $offset / ($earthRadius * cos(M_PI * $coord[0] / 180));
			return $coord[$what] + $radOff * 180 / M_PI;
		}

		public static function bfi_getBBox_openstreetmap($lat, $lon, $area) {
			$offset = $area / 2;
			return [
				0 => self::bfi_getCoordOffset_openstreetmap(1, $lat, $lon, -$offset),
				1 => self::bfi_getCoordOffset_openstreetmap(0, $lat, $lon, -$offset),
				2 => self::bfi_getCoordOffset_openstreetmap(1, $lat, $lon, $offset),
				3 => self::bfi_getCoordOffset_openstreetmap(0, $lat, $lon, $offset),
				4 => $lat,
				5 => $lon
			]; // 0 = minlon, 1 = minlat, 2 = maxlon, 3 = maxlat, 4,5 = original val (marker)
		}

		public static function bfi_getRandomString($lenght){
			$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			return substr(str_shuffle($permitted_chars), 0, $lenght);
		}
//----------------------------------
//	END openstreemap functions
//----------------------------------
//----------------------------------
//	tripadvisor functions
//----------------------------------
		public static function bfi_getWidget_tripadvisor($id, $theme=0, $language='') {
			if ($language==null) {
				$language = $GLOBALS['bfi_lang'];

			}

			$uniq = mt_rand(100, 999);
			$uniqUl = self::bfi_getRandomString(7);//  "v33MNVc";
			$uniqLi = self::bfi_getRandomString(10);//  "xJHIuTXoQj";
			$html="";
			if ($theme==0) {//pallini e n recensioni e logo
			    $html = '<div id="TA_cdsratingsonlynarrow'.$uniq.'" class="TA_cdsratingsonlynarrow">
							<ul id="'.$uniqUl.'" class="TA_links uiKOkdiQ">
								<!-- <li id="'.$uniqLi.'" class="cBeNhA">
									<a target="_blank" href="https://www.tripadvisor.com/">
										<img src="https://www.tripadvisor.com/img/cdsi/img2/branding/tripadvisor_logo_transp_340x80-18034-2.png" alt="TripAdvisor"/>
									</a>
								</li> -->
							</ul>
						</div>
							<script async src="https://www.jscache.com/wejs?wtype=cdsratingsonlynarrow&amp;uniq='.$uniq.'&amp;locationId='.$id.'&amp;lang='.$language.'&amp;border=false&amp;display_version=2" data-loadtrk onload="this.loadtrk=true">
						</script>
					';
			}
			if ($theme==1) {//logo + pallini
			    $html = '
						<div id="TA_socialButtonBubbles'.$uniq.'" class="TA_socialButtonBubbles">
							<ul id="'.$uniqUl.'" class="TA_links VeePpAeU23">
								<li id="'.$uniqLi.'" class="P4zUGpEA">
									<a target="_blank" href="https://www.tripadvisor.com">
										<img src="https://www.tripadvisor.com/img/cdsi/img2/branding/socialWidget/20x28_green-21693-2.png"/>
									</a>
								</li>
							</ul>
						</div>
						<script async src="https://www.jscache.com/wejs?wtype=socialButtonBubbles&amp;uniq='.$uniq.'&amp;locationId='.$id.'&amp;color=green&amp;size=rect&amp;lang='.$language.'&amp;display_version=2" data-loadtrk onload="this.loadtrk=true"></script>
					';

			}
			if ($theme==2) { // lista recensioni
			    $html = '
					  <div id="TA_selfserveprop'.$uniq.'" class="TA_selfserveprop">
						<ul id="'.$uniqUl.'" class="TA_links BgWbhd">
							<li id="'.$uniqLi.'" class="BLJJ8OKjOT1Z">
								<a target="_blank" href="https://www.tripadvisor.it/">
									<img src="https://www.tripadvisor.it/img/cdsi/img2/branding/150_logo-11900-2.png" alt="TripAdvisor"/>
								</a>
							</li>
						</ul>
					</div>
					<script async src="https://www.jscache.com/wejs?wtype=selfserveprop&amp;uniq='.$uniq.'&amp;locationId='.$id.'&amp;lang='.$language.'&amp;rating=true&amp;nreviews=4&amp;writereviewlink=true&amp;popIdx=false&amp;iswide=true&amp;border=false&amp;display_version=2" data-loadtrk onload="this.loadtrk=true"></script>
					';
			}
			return $html;
		}

//----------------------------------
//	END tripadvisor functions
//----------------------------------
//----------------------------------
//	country functions
//----------------------------------
		public static function bfi_get_country_name_by_code( $country_code ) {
			if (empty($country_code) && strlen ($country_code) < 2) {
			    return false;
			}
			if (strlen ($country_code) > 2) {
				$country_code = substr($country_code,0,2);
			}
			$country_code = strtoupper($country_code);
			return ( isset( self::$listCountries[$country_code] ) ? self::$listCountries[$country_code] : false );
		}

		public static function bfi_get_country_code_by_name( $country_name ) {
			if (empty($country_name) ) {
			    return false;
			}
			return ( in_array ( $country_name , self::$listCountries ) ? array_search($country_name,  self::$listCountries) : false );
		}

//----------------------------------
//	END country functions
//----------------------------------
		public static function GetSettingValue($array, $data){
			if(!empty( $array )){
				foreach($array as $currObj) {
				   if( isset($currObj->SettingKey) && $currObj->SettingKey == $data) {
					   return strtolower($currObj->SettingValue)==='true';
					   }
				}
			}
			return false;
		}

		public static function getPageId($refpage){
			$currPage = get_post( bfi_get_page_id( $refpage ) );
			if (!empty($currPage )) {
				return $currPage->ID;
			}
			return 0;
		}

		public static function getPageUrl($refpage){
			$currPageId = self::getPageId( $refpage );
			return self::getPageUrlbyId($currPageId);
		}

		public static function getPageUrlbyId($currPageId){
			if (!empty($currPageId )) {
				$currUrl =  get_permalink( $currPageId);
				if(substr($currUrl , -1)!=='/'){
					$currUrl .= '/';
				}
				return $currUrl;
			}
			return '';
		}

		public static function getPageUrlbyIdtranslated($currPageId){
			if( isset($currPageId) && defined( 'POLYLANG_VERSION' ) ) {
				$currPageId = pll_get_post( $currPageId );				
			}
			
			$currPage = get_post( $currPageId );
			if (!empty($currPage )) {
				$currUrl =  get_permalink( $currPage->ID);
				if(substr($currUrl , -1)!=='/'){
					$currUrl .= '/';
				}
				return $currUrl;
			}
			return '';
		}

		public static function is_dir_empty($dir) {
			if (!is_readable($dir)) return NULL; 
			return (count(scandir($dir)) == 2);
		}

		public static function GetDateTimeFromDurationDiff($date, $strDiff, $inverse = true) {
			if(empty($strDiff)) return $date;
			
			preg_match('/^((?<years>\d+)y)?((?<months>\d+)M)?((?<days>\d+)d)?((?<hours>\d+)h)?((?<minutes>\d+)m)?((?<seconds>\d+)s)?/', $strDiff, $matches);
			
			$ret = clone $date;
			foreach ($matches as $key => $value) {
				if (empty($value)) continue;
			    if ($key === "seconds") $ret->modify(($inverse ? "-" : "+") . $value . " seconds");
			    if ($key === "minutes") $ret->modify(($inverse ? "-" : "+") . $value . " minutes");
			    if ($key === "hours") $ret->modify(($inverse ? "-" : "+") . $value . " hours");
			    if ($key === "days") $ret->modify(($inverse ? "-" : "+") . $value . " days");
			    if ($key === "months") $ret->modify(($inverse ? "-" : "+") . $value . " months");
			    if ($key === "years") $ret->modify(($inverse ? "-" : "+") . $value . " years");
			}
			
			return $ret;
		}
		
		public static function GetDurationString($strDiff, $delimiter = ', ', $approx= false) {
			if(empty($strDiff)) return "";
			
			preg_match('/^((?<years>\d+)y)?((?<months>\d+)M)?((?<days>\d+)d)?((?<hours>\d+)h)?((?<minutes>\d+)m)?((?<seconds>\d+)s)?/', $strDiff, $matches);
			$strValues = array();
			$strapprox = "";
			foreach ($matches as $key => $value) {
				if (empty($value)) continue;
			    if ($key === "seconds"){ 
					$strValues[] = sprintf(__(' %d second/s', 'bfi'), $value);
				if ($approx) {
					    $strapprox = " " . __('(Approx.)', 'bfi');
					}
				}
			    if ($key === "minutes"){ 
					$strValues[] =  sprintf(( $value>1 ? __(' %d minutes', 'bfi') : __(' %d minute', 'bfi') ), $value);
					if ($approx) {
					    $strapprox = " " . __('(Approx.)', 'bfi');
					}
				}
			    if ($key === "hours"){  
					$strValues[] = sprintf(($value>1 ? __(' %d hours', 'bfi') : __(' %d hour', 'bfi')), $value);
				}
			    if ($key === "days"){
					$strValues[] = sprintf(($value>1 ? __(' %d days', 'bfi') : __(' %d day', 'bfi')), $value); 
				}
			    if ($key === "months") $strValues[] = sprintf(__(' %d month/s', 'bfi'), $value);
			    if ($key === "years") $strValues[] = sprintf(__(' %d year/s', 'bfi'), $value);
			}
			
			return implode($delimiter, $strValues) . $strapprox ;
		}

		public static function GetDurationStringFromSpan($currValue, $baseTimeUnit = "m", $enabledFields = null) {
			
			$a=1;
			$fieldsMultiplier = array();
			$validMultipliers = array();
			$valueStrings = array();
			$fieldsMultiplier["y"] = 60 * 60 * 24 * 365;
			$fieldsMultiplier["M"] = 60 * 60 * 24 * 31;
			$fieldsMultiplier["d"] = 60 * 60 * 24;
			$fieldsMultiplier["h"] = 60 * 60;
			$fieldsMultiplier["m"] = 60;
			$fieldsMultiplier["s"] = 1;

			$fieldKeys = array_keys($fieldsMultiplier);
			if (!isset($enabledFields) || empty($enabledFields)) $enabledFields = $fieldKeys;
		   
			$validFields = array_slice($fieldKeys, 0, array_search($baseTimeUnit, $fieldKeys) + 1);
		   
			foreach ($validFields as $unit) {
				$validMultipliers[$unit] = $fieldsMultiplier[$unit] / $fieldsMultiplier[$baseTimeUnit];
			}
			
			foreach ($validFields as $unit) {
				$multiplier = $validMultipliers[$unit];
				$stringSuffix = $unit;
				$unitValue = floor($currValue / $multiplier);
				if(array_search($unit, $enabledFields) > -1 && $unitValue > 0) {
					$valueStrings[] = $unitValue . "" . $unit;
				}
				$currValue = $currValue % $multiplier;

			}
			return implode('', $valueStrings);
		}

	}
	class MyDateTime extends \DateTime implements \JsonSerializable
	{
		public function __construct(DateTime $dt) {
			parent::__construct($dt->format("r"));
		}
		
		public function jsonSerialize()
		{
			return $this->format("Y-m-d\Th:m:s");
		}
	}
}
