<?php 

function getOS($userAgent) {
    $oses = array (
		// First of all detect Mobile Devices
		'Windows Phone' => 'Windows Phone',
		'Android' => '(Android)',
		'iPod' => '(iPod)',
		'iPhone' => '(iPhone)',
		'iPad' => '(iPad)',
		'BlackBerry' => 'BlackBerry',
		// Windows Operating Systems
        'Windows 3.11' => 'Win16',
        'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
        'Windows 98' => '(Windows 98)|(Win98)',
        'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
		'Windows 2000 Service Pack 1' => '(Windows NT 5.01)',
        'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
        'Windows 2003' => '(Windows NT 5.2)',
        'Windows Vista' => '(Windows NT 6.0)|(Windows Vista)',
        'Windows 7' => '(Windows NT 6.1)|(Windows 7)',
		'Windows 8' => '(Windows NT 6.2)|(Windows 8)',
		'Windows 10' => '(Windows NT 10.0; WOW64)',
        'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
        'Windows ME' => '(Windows ME)|(Windows 98; Win 9x 4.90 )',
		'Windows CE' => '(Windows CE)',
		// UNIX Like Operating Systems
		'Mac OS X Kodiak (beta)' => '(Mac OS X beta)',
		'Mac OS X Cheetah' => '(Mac OS X 10.0)',
		'Mac OS X Puma' => '(Mac OS X 10.1)',
		'Mac OS X Jaguar' => '(Mac OS X 10.2)',
		'Mac OS X Panther' => '(Mac OS X 10.3)',
		'Mac OS X Tiger' => '(Mac OS X 10.4)',
		'Mac OS X Leopard' => '(Mac OS X 10.5)',
		'Mac OS X Snow Leopard' => '(Mac OS X 10.6)',
		'Mac OS X Lion' => '(Mac OS X 10.7)',
		'Mac OS X' => '(Mac OS X)',
		'Mac OS' => '(Mac_PowerPC)|(PowerPC)|(Macintosh)',
		'Open BSD' => '(OpenBSD)',
		'SunOS' => '(SunOS)',
		'Solaris 11' => '(Solaris/11)|(Solaris11)',
		'Solaris 10' => '((Solaris/10)|(Solaris10))',
		'Solaris 9' => '((Solaris/9)|(Solaris9))',
		'CentOS' => '(CentOS)',
		'QNX' => '(QNX)',
		// Kernels
		'UNIX' => '(UNIX)',
		// Linux Operating Systems
		'Ubuntu 12.10' => '(Ubuntu/12.10)|(Ubuntu 12.10)',
		'Ubuntu 12.04 LTS' => '(Ubuntu/12.04)|(Ubuntu 12.04)',
		'Ubuntu 11.10' => '(Ubuntu/11.10)|(Ubuntu 11.10)',
		'Ubuntu 11.04' => '(Ubuntu/11.04)|(Ubuntu 11.04)',
		'Ubuntu 10.10' => '(Ubuntu/10.10)|(Ubuntu 10.10)',
		'Ubuntu 10.04 LTS' => '(Ubuntu/10.04)|(Ubuntu 10.04)',
		'Ubuntu 9.10' => '(Ubuntu/9.10)|(Ubuntu 9.10)',
		'Ubuntu 9.04' => '(Ubuntu/9.04)|(Ubuntu 9.04)',
		'Ubuntu 8.10' => '(Ubuntu/8.10)|(Ubuntu 8.10)',
		'Ubuntu 8.04 LTS' => '(Ubuntu/8.04)|(Ubuntu 8.04)',
		'Ubuntu 6.06 LTS' => '(Ubuntu/6.06)|(Ubuntu 6.06)',
		'Red Hat Linux' => '(Red Hat)',
		'Red Hat Enterprise Linux' => '(Red Hat Enterprise)',
		'Fedora 17' => '(Fedora/17)|(Fedora 17)',
		'Fedora 16' => '(Fedora/16)|(Fedora 16)',
		'Fedora 15' => '(Fedora/15)|(Fedora 15)',
		'Fedora 14' => '(Fedora/14)|(Fedora 14)',
		'Chromium OS' => '(ChromiumOS)',
		'Google Chrome OS' => '(ChromeOS)',
		// Kernel
		'Linux' => '(Linux)|(X11)',
		// BSD Operating Systems
		'OpenBSD' => '(OpenBSD)',
		'FreeBSD' => '(FreeBSD)',
		'NetBSD' => '(NetBSD)',
		//DEC Operating Systems
		'OS/8' => '(OS/8)|(OS8)',
		'Older DEC OS' => '(DEC)|(RSTS)|(RSTS/E)',
		'WPS-8' => '(WPS-8)|(WPS8)',
		// BeOS Like Operating Systems
		'BeOS' => '(BeOS)|(BeOS r5)',
		'BeIA' => '(BeIA)',
		// OS/2 Operating Systems
		'OS/2 2.0' => '(OS/220)|(OS/2 2.0)',
		'OS/2' => '(OS/2)|(OS2)',
		// Search engines
		'nuhk' => '(nuhk)',
		'Googlebot' => '(Googlebot)',
		'Yammybot' => '(Yammybot)',
		'Openbot' => '(Openbot)',
		'Slurp' => '(Slurp)',
		'msnbot' => '(msnbot)',
		'Ask Jeeves/Teoma' => '(Ask Jeeves/Teoma)',
		'ia_archiver' => '(ia_archiver)'
    );
    foreach($oses as $os=>$pattern){
        if(preg_match("/$pattern/i", $userAgent)) { 
            return $os;
        }
    }
    return 'Unknown'; 
}

$get_ip = getenv("REMOTE_ADDR");											/* IP */
$get_user_agent = getenv("HTTP_USER_AGENT");											
$get_os = getOS($get_user_agent);											/* Operation System */
$get_port = getenv("REMOTE_PORT");											/* Port */
$get_connect = $_SERVER['HTTP_CONNECTION'];									/* Host */
$get_host = gethostbyaddr(getenv("REMOTE_ADDR"));							/* Connection */
$get_referer = @$_SERVER['HTTP_REFERER'];									/* Referer */


/* Формируем JavaScript код, который будет тащить все возможные данные о клиенте юзера */
$script = "<script language='javascript' type='text/javascript'>

	// ========================================================================
	// CLIENT INFO
	// ========================================================================
	var client = new Array();
	client['detected_by_server'] = new Array();
	client['detected_by_browser'] = new Array();

	client['detected_by_server']['ip'] = '".$get_ip."';
	client['detected_by_server']['user_agent'] = '".$get_user_agent."';
	client['detected_by_server']['os'] = '".$get_os."';
	client['detected_by_server']['port'] = '".$get_port."';
	client['detected_by_server']['host'] = '".$get_host."';
	client['detected_by_server']['referer'] = '".$get_referer."';
			
	client['detected_by_browser']['language'] = navigator.language;
	client['detected_by_browser']['screen_width'] = screen.width;
	client['detected_by_browser']['screen_height'] = screen.height;	
</script>";

echo $script;

?>

