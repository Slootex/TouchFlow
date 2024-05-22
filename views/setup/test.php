<?php
@session_start();

// Database credentials
$host = "127.0.0.1:3307";
$username = "root";
$password = "6b2uf&25A2w*";
$dbName = "db_crmv2";
$tablename = "webinar_progress";

// COUNTER FOR KURSE
$kurseCounter = 0;

// KURSE LISTE
$kurse = [
	["id" => "0", "name" => "Einführung", "completed" => 0],
	["id" => "1", "name" => "Packtisch Einlagern", "completed" => 0],
	["id" => "2", "name" => "Packtisch Auslagern", "completed" => 0]
];

// VIDEO LISTE
$videos = [
	[// id == 0
		["id" => "0", "name" => "Video 1", "path" => "/uploads/company/1/video/Test1.mp4", "completed" => 0],
		["id" => "1", "name" => "Video 2", "path" => "/uploads/company/1/video/Test1.mp4", "completed" => 0],
	],
	[// id == 1
		["id" => "0", "name" => "Video 1", "path" => "/uploads/company/1/video/Test1.mp4", "completed" => 0],
		["id" => "1", "name" => "Video 2", "path" => "/uploads/company/1/video/Test1.mp4", "completed" => 0],
	],
	[// id == 2
		["id" => "0", "name" => "Video 1", "path" => "/uploads/company/1/video/Test1.mp4", "completed" => 0],
		["id" => "1", "name" => "Video 2", "path" => "/uploads/company/1/video/Test1.mp4", "completed" => 0],
	]
];

// Ausrechnen der Prozentanzahl für die Progressbar
$maxVideos = 0;
foreach($videos as $video) {
	$maxVideos += count($video);
}


// Connect to the database
$conn = new mysqli($host, $username, $password, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get progress for a specific user
function getWatchedVideos($admin_id, $conn, $videos) {

    $sql = "SELECT *  FROM webinar_progress WHERE admin_id = $admin_id";
    $result = $conn->query($sql);

	
    if ($result->num_rows > 0) {
    	while($row = $result->fetch_assoc()) {
    		
    		$videos[$row["main_video_id"]][$row["video_id"]]["completed"] = 1;
		}
		
        return [$videos];
    } else {
        return 0;
    }
}

$admin_id = $_SESSION["admin"]["id"]; // Use admin_id from session
$watchedVideos = getWatchedVideos($admin_id, $conn, $videos);
$videos = $watchedVideos[0];

if(isset($_POST["post_type"])) {
	if($_POST["post_type"] == "set_video_marked") {
		
		$video_id		= $_POST["video_id"];
		$main_video_id	= $_POST["main_video_id"];
		
	    $sql		= "insert into $tablename (admin_id, video_id, main_video_id) values($admin_id, $video_id, $main_video_id)";
	    echo $sql;
	    $result 	= $conn->query($sql);
	}
}

$conn->close();
?>


<!-- HTML CODE -->
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deine Schulung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f8; color: #333; }
        .container { padding-top: 40px; }
        #videoContainer { margin-top: 20px; display: none; }
        @media (max-width: 768px) { .container { padding: 20px; } }
    </style>
</head>
<script>

	let selectedKurs = "";
	function selectKurs(kurs, kurs_name) {
		document.getElementById("kurse-container").classList.add("hidden");
		document.getElementById("go-back-button").classList.remove("hidden");
		document.getElementById("kurs-" + kurs).classList.remove("hidden");
		document.getElementById("header").innerHTML = kurs_name;
		document.getElementById("header-small").innerHTML = "3 von 5 Lektionen abgeschlossen";
		
		selectedKurs = kurs;
	}
	
	function goBack() {
		document.getElementById("kurse-container").classList.remove("hidden");
		document.getElementById("go-back-button").classList.add("hidden");
		document.getElementById("kurs-" + selectedKurs).classList.add("hidden");
		document.getElementById("video-container").classList.add("hidden");
		
		if(selectedVideo != "") {
			if(document.getElementById("kurs-"+selectedKurs+"-video-"+selectedVideo).style.backgroundColor != "rgb(195, 250, 196)") {
				document.getElementById("kurs-"+selectedKurs+"-video-"+selectedVideo).style.backgroundColor = "";
			}
		}
		
		selectedVideo = "";
		selectedKurs = "";
		
		document.getElementById("header").innerHTML = "Steubel Steuergeräte";
		document.getElementById("header-small").innerHTML = "2 von 15 Lektionen abgeschlossen";
		
	}
	
	let selectedVideo = "";
	let selectedVideoName = "";
	function selectVideo(id, path, kurs, videoName) {
		document.getElementById("video-source").setAttribute('src', path);
		
		document.getElementById("video-main").load();
		document.getElementById("video-main").play();
		
		document.getElementById("kurs-"+kurs).classList.add("hidden");
		document.getElementById("side-card").classList.add("hidden");

		selectedVideo = id;
		selectedVideoName = videoName;
		
		document.getElementById("video-container").classList.remove("hidden");
	}
	
	function setAsMarked() {
		document.getElementById("kurs-"+selectedKurs+"-video-"+selectedVideo).style.backgroundColor = "#c3fac4";
		document.getElementById("kurs-"+selectedKurs+"-video-"+selectedVideo).innerHTML = selectedVideoName + " - Abgeschlossen";
		
		$.post( "", { post_type: "set_video_marked", video_id: selectedVideo, main_video_id: selectedKurs } );
	}
	
	let videos = <?php echo json_encode($videos);?>;

</script>
<body class="px-10">
	<div class="absolute left-20 top-24 hidden" id="go-back-button">
		<svg onclick="goBack()" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 hover:text-blue-400 cursor-pointer">
		  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
		</svg>
	</div>

	<div class="px-6 pb-10 -mt-1" style="border: solid #d1d1d1 1px; border-radius: 4px">
		<div class="mt-6">
			<h1 class="text-4xl font-semibold" id="header" >Steubel Steuergeräte</h1>
			<p class="text-zinc-500" id="header-small" >2 von 15 Lektionen abgeschlossen</p>
		</div>
		<div class="absolute right-16 top-6">
			<img style="width: 35%; height: 23%;" class="bg-blue-600 rounded-md px-4 py-2 float-right" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png" >
		</div>
	</div>
    
    <div class="">
	    <div id="kurse-container" class="mt-10 p-6 float-left" style="height: 23rem; border: solid #d1d1d1 1px; border-radius: 4px; width: 66%;">
	        <div class="">
	        	<?php foreach($kurse as $kurs): ?>
	        		<div class="mb-6" onclick="selectKurs('<?php echo $kurs["id"]; ?>', '<?php echo $kurs["name"]; ?>')">
	        			<div class="flex">
	        				<img style="width: 22%; height: 5.5rem;" class="bg-blue-600 rounded-md px-4 py-2 float-right" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png" >
	        				<div class="mt-3 pt-0.5 ml-3">
	        					<p class="text-lg font-bold tracking-wider"><?php echo $kurs["name"]; ?></p>
	        					<div class="flex -mt-4">
	        						<p class="text-gray-600 mr-3 text-sm">3 von 5 Lektionen abgeschlossen</p>
	        						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 text-gray-600 mt-1.5">
									  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
									</svg>
									<p class="text-gray-600 ml-1 text-sm">1h 35m</p>
	        					</div>
	        				</div>
	        			</div>
	        		</div>
	        		<?php $kurseCounter++; ?>
	        	<?php endforeach; ?>
	        </div>
	    </div>
	    
	    <div class="py-6 px-8 mt-10 float-right" id="side-card"  style="height: 23rem; border: solid #d1d1d1 1px; border-radius: 4px; width: 30.5%;">
	    	<img class="bg-blue-600 rounded-md px-4 py-2 w-full" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png" >
	    	<p class="text-center mt-2 text-2xl font-bold tracking-wider">Steubel</p>
	    	<p class="text-gray-400 text-md">Bei Fragen wenden Sie sich an Steubel Steugergeträte über nawd ad awd aw </p>
	    </div>
    </div>
    
    <?php $kurseCounter = 0; ?>
    <?php foreach($kurse as $kurs): ?>
    	<div class="mt-10 px-6 pt-0 float-left hidden" style="height: 23rem; border: solid #d1d1d1 1px; border-radius: 4px; width: 66%;" id="kurs-<?php echo $kurseCounter ?>">
	        <div class="">
	        	<?php foreach($videos[$kurseCounter] as $video): ?>
	        		<div class="mt-6" id="kurs-<?php echo $kurseCounter; ?>-video-<?php echo $video["id"] ?>"  onclick="selectVideo('<?php echo $video["id"]; ?>', '<?php echo $video["path"]; ?>', '<?php echo $kurseCounter; ?>', '<?php echo $video["name"]; ?>')">
	        			<div class="flex">
	        				<img style="width: 22%; height: 5.5rem;" class="bg-blue-600 rounded-md px-4 py-2 float-right" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png" >
	        				<div class="mt-3 pt-0.5 ml-3">
	        					<p class="text-lg font-bold tracking-wider"><?php echo $video["name"]; ?></p>
	        					<div class="flex -mt-4">
	        						<p class="text-gray-600 mr-3 text-sm">3 von 5 Lektionen abgeschlossen</p>
	        						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 text-gray-600 mt-1.5">
									  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
									</svg>
									<p class="text-gray-600 ml-1 text-sm">1h 35m</p>
	        					</div>
	        				</div>
	        			</div>
	        		</div>
	        		
	        	<?php endforeach; ?>
	        </div>
    	</div>
    	<?php $kurseCounter++; ?>
    <?php endforeach; ?>
    
    <div class="hidden mt-12" id="video-container" >
    	<div class="">
    		<div class="">
    			<video id="video-main" class="rounded-xl float-left" style="width: 63%; height: 50%; object-fit: cover;" controls>
	    			<source src="" type="video/mp4" id="video-source">
	    		</video>
	    		
	    		<div class="py-3 px-4 float-right" style="height: 40vw; border: solid #d1d1d1 1px; border-radius: 4px; width: 34%;">
	    			<p class="text-2xl font-bold">Einführung: Video 1</p>
	    			<p class="text-zinc-400 -mt-4">Lektion 1 von 5</p>
	    			
	    			<div class="grid grid-row-1 overflow-auto" style="height: 32vw">
	    				<div class="flex">
	    					<img style="width: 50%; height: 5rem" class="bg-blue-600 rounded-md px-4 py-2" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png">
	    					<p class="ml-3 text-zinc-500 mt-4">Pre Onboarding</p>
	    				</div>
	    				
	    				<div class="flex mt-6">
	    					<img style="width: 50%; height: 5rem" class="bg-blue-600 rounded-md px-4 py-2" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png">
	    					<p class="ml-3 text-zinc-500 mt-4">Pre Onboarding</p>
	    				</div>
	    				
	    				<div class="flex mt-6">
	    					<img style="width: 50%; height: 5rem" class="bg-blue-600 rounded-md px-4 py-2" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png">
	    					<p class="ml-3 text-zinc-500 mt-4">Pre Onboarding</p>
	    				</div>
	    				
	    				<div class="flex mt-6">
	    					<img style="width: 50%; height: 5rem" class="bg-blue-600 rounded-md px-4 py-2" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png">
	    					<p class="ml-3 text-zinc-500 mt-4">Pre Onboarding</p>
	    				</div>
	    				
	    				<div class="flex mt-6">
	    					<img style="width: 50%; height: 5rem" class="bg-blue-600 rounded-md px-4 py-2" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png">
	    					<p class="ml-3 text-zinc-500 mt-4">Pre Onboarding</p>
	    				</div>
	    				
	    				<div class="flex mt-6">
	    					<img style="width: 50%; height: 5rem" class="bg-blue-600 rounded-md px-4 py-2" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png">
	    					<p class="ml-3 text-zinc-500 mt-4">Pre Onboarding</p>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    	<div>
	    		<div class=" mt-10 pt-6 grid grid-cols-3" style="width: 63%">
	    			<div class="col-span-2 flex">
		    			<img style="height: 5rem; width: 30%" class="bg-blue-600 rounded-md px-4 py-2" src="https://crm-v2.eversafe.synology.me/uploads/company/1/img/logo_login_steubel.png">
		    			<div class="ml-4 mt-2.5" >
		    				<p class=" text-zinc-500">Gorßartig! Weiter geht's</p>
		    				<p class="-mt-3 font-bold text-lg">Die 5 WIchtigesten hurensohn penis</p>
		    			</div>
	    			</div>
	    			<div class="flaot-right w-full h-full">
	    				<button class="mt-2 bg-blue-600 hover:bg-blue-400 rounded-md text-white px-2 pt-3 text-center font-medium text-md float-right flex">
	    					<p>Nächste Lektion</p>
	    					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 ml-1 mt-0.5">
							  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
							</svg>
	    				</button>
	    			</div>
	    		</div>
	    	</div>
	    	
	    	<div class="mt-4 float-left" style="height: 40vw; border: solid #d1d1d1 1px; border-radius: 4px; width: 63%;">
	    	</div>
	    	<div class="mt-2 float-right px-4 py-2" style="height: 40vw; border: solid #d1d1d1 1px; border-radius: 4px; width: 34%;">
	    		<p class="text-xl font-bold">Anhänge</p>
	    	</div>
	    	
	    	
	    <div class="w-full h-full " style="margin-top: 100rem">
	    </div>
    	</div>	
    	</div>
    </div>
</body>
</html>