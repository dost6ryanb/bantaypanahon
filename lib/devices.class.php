<?php
//include_once 'lib/SPSQLite.class.php';

class Devices {
	

	public static function getAllDevices() {
		//$strDatabaseFile = str_replace("\\", "/", $_ENV["S2G_DB_PATH"]);

		$connection = new PDO("sqlite:sqlite.db");

		$query = 'select devices.dev_id, provinces.name as province_name, districts.name as district_name, municipalities.name as municipality_name, locations.name as location_name, types.name as type_name, projects.name as project_name, devices.posx, devices.posy, devices.status_id '.
				 'from devices '.
				 'left outer join provinces on devices.province_id = provinces.id '.
				 'left outer join districts on devices.district_id = districts.id '.
				 'left outer join municipalities on devices.municipality_id = municipalities.id '.
				 'left outer join locations on devices.location_id = locations.id '.
				 'left outer join types on devices.type_id = types.id '.
				 'left outer join projects on devices.project_id = projects.id '.
				 'order by province_name, district_name, municipality_name ASC';

		return $connection->query($query)->fetchAll(PDO::FETCH_ASSOC);
		

	}

	public static function GetAllDevicesWithParameter($param) {

		$connection = new PDO("sqlite:sqlite.db");

		$types = '';
		switch ($param) {
			case 'Waterlevel' :
				$types ="'Waterlevel', 'Waterlevel & Rain 2'";
				break;
			case 'Rainfall' : 
				$types  = "'VAISALA', 'Rain1', 'Rain2', 'Waterlevel & Rain 2', 'UAAWS', 'BSWM_Lufft'";
				break;
			case 'Temperature' :
				$types  = "'VAISALA', 'UAAWS', 'BSWM_Lufft'";
				break;
			default :
				return FALSE;
		}

		$query = 'select devices.dev_id, provinces.name as province_name, districts.name as district_name, municipalities.name as municipality_name, locations.name as location_name, types.name as type_name, projects.name as project_name, devices.posx, devices.posy, devices.status_id '.
				 'from devices '.
				 'left outer join provinces on devices.province_id = provinces.id '.
				 'left outer join districts on devices.district_id = districts.id '.
				 'left outer join municipalities on devices.municipality_id = municipalities.id '.
				 'left outer join locations on devices.location_id = locations.id '.
				 'left outer join types on devices.type_id = types.id '.
				 'left outer join projects on devices.project_id = projects.id '.
				 'where types.name in ('.$types.')'.
				 'order by province_name, district_name, municipality_name ASC';
		

		return $connection->query($query)->fetchAll(PDO::FETCH_ASSOC);

	}

	public static function updateStatusId($dev_id, $status_id) {
		$connection = new PDO("sqlite:sqlite.db");

		$query = 'update devices '
				 .'set status_id='.$status_id
				 .' where dev_id='.$dev_id;

		$rowaffected = $connection->exec($query);
		if ($rowaffected > 0) {
			return true;
		} else {
			return false;
		} 

	}	
}

?>