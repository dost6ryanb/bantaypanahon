<?php
//include_once 'lib/SPSQLite.class.php';

class Devices {
	

	public static function GetDevicesAll() {
		//$strDatabaseFile = str_replace("\\", "/", $_ENV["S2G_DB_PATH"]);

		$connection = new PDO("sqlite:database/sqlite.db");

		$query = 'SELECT * from v_devices';

		return $connection->query($query)->fetchAll(PDO::FETCH_ASSOC);
		
	}

	public static function getAllDevices() {
		//$strDatabaseFile = str_replace("\\", "/", $_ENV["S2G_DB_PATH"]);

		$connection = new PDO("sqlite:database/sqlite.db");

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

	public static function GetDevicesByParam($param) {
		$connection = new PDO("sqlite:database/sqlite.db");

		$query = '';

		switch ($param) {
			case 'Waterlevel' :
				$types ="'Waterlevel', 'Waterlevel & Rain 2'";

				$query = 'select v.*, normal, overflow, device_height, riverindex from v_devices v '.
						'left outer join waterlevelinfo w on v.dev_id = w.devices_dev_id '.
						'where v.type in ('. $types .') '.
						'order by v.province, w.riverindex IS NULL, w.riverindex ASC';
				break;
			case 'Rainfall' : 
				$types  = "'VAISALA', 'Rain1', 'Rain2', 'Waterlevel & Rain 2', 'UAAWS', 'BSWM_Lufft', 'Davis'";
				
				$query = 'select v.* from v_devices v '.
						'where v.type in ('. $types .') '.
						'order by v.province, v.district, v.municipality ASC';
				break;
			case 'Temperature' :
				$types  = "'VAISALA', 'UAAWS', 'BSWM_Lufft', 'Davis'";
				
				$query = 'select v.* from v_devices v '.
						'where v.type in ('. $types .') '.
						'order by v.province, v.district, v.municipality ASC';
				break;
			default :
				$query = 'SELECT * from v_devices v order by province, district, municipality ASC';
		}

		return $connection->query($query)->fetchAll(PDO::FETCH_ASSOC);
	}

    public static function GetDeviceIdsByParam($param) {
        $connection = new PDO("sqlite:database/sqlite.db");

        $query = '';

        switch ($param) {
            case 'Waterlevel' :
                $types ="'Waterlevel', 'Waterlevel & Rain 2'";

                $query = 'select v.dev_id from v_devices v '.
                    'left outer join waterlevelinfo w on v.dev_id = w.devices_dev_id '.
                    'where v.type in ('. $types .') '.
                    'order by v.province, w.riverindex IS NULL, w.riverindex ASC';
                break;
            case 'Rainfall' :
                $types  = "'VAISALA', 'Rain1', 'Rain2', 'Waterlevel & Rain 2', 'UAAWS', 'BSWM_Lufft', 'Davis'";

                $query = 'select v.dev_id from v_devices v '.
                    'where v.type in ('. $types .') '.
                    'order by v.province, v.district, v.municipality ASC';
                break;
            case 'Temperature' :
                $types  = "'VAISALA', 'UAAWS', 'BSWM_Lufft', 'Davis'";

                $query = 'select v.dev_id from v_devices v '.
                    'where v.type in ('. $types .') '.
                    'order by v.province, v.district, v.municipality ASC';
                break;
            default :
                $query = 'SELECT v.dev_id from v_devices v order by province, district, municipality ASC';
        }

        return $connection->query($query)->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    public static function GetEnabledDeviceIdsByParam($param) {
        $connection = new PDO("sqlite:database/sqlite.db");

        $query = 'select v.dev_id from v_devices v ';
        $query_status = 'v.status <> "1" ';

        switch ($param) {
            case 'Waterlevel' :
                $types ="'Waterlevel', 'Waterlevel & Rain 2'";

                $query .= 'left outer join waterlevelinfo w on v.dev_id = w.devices_dev_id '.
                    "where v.type in ($types) AND $query_status".
                    'order by v.province, w.riverindex IS NULL, w.riverindex ASC';
                break;
            case 'Rainfall' :
                $types  = "'VAISALA', 'Rain1', 'Rain2', 'Waterlevel & Rain 2', 'UAAWS', 'BSWM_Lufft', 'Davis'";

                $query .= "where v.type in ($types) AND $query_status".
                    'order by v.province, v.district, v.municipality ASC';
                break;
            case 'Temperature' :
                $types  = "'VAISALA', 'UAAWS', 'BSWM_Lufft', 'Davis'";

                $query .= "where v.type in ($types) AND $query_status".
                    'order by v.province, v.district, v.municipality ASC';
                break;
            default :
                $query .= 'where $query_status order by province, district, municipality ASC';
        }

        return $connection->query($query)->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    public static function GetDisabledDeviceIdsByParam($param) {
        $connection = new PDO("sqlite:database/sqlite.db");

        $query = 'select v.dev_id from v_devices v ';
        $query_status = 'v.status == "1" ';

        switch ($param) {
            case 'Waterlevel' :
                $types ="'Waterlevel', 'Waterlevel & Rain 2'";

                $query .= 'left outer join waterlevelinfo w on v.dev_id = w.devices_dev_id '.
                    "where v.type in ($types) AND $query_status".
                    'order by v.province, w.riverindex IS NULL, w.riverindex ASC';
                break;
            case 'Rainfall' :
                $types  = "'VAISALA', 'Rain1', 'Rain2', 'Waterlevel & Rain 2', 'UAAWS', 'BSWM_Lufft', 'Davis'";

                $query .= "where v.type in ($types) AND $query_status".
                    'order by v.province, v.district, v.municipality ASC';
                break;
            case 'Temperature' :
                $types  = "'VAISALA', 'UAAWS', 'BSWM_Lufft', 'Davis'";

                $query .= "where v.type in ($types) AND $query_status".
                    'order by v.province, v.district, v.municipality ASC';
                break;
            default :
                $query .= 'where $query_status order by province, district, municipality ASC';
        }

        return $connection->query($query)->fetchAll(PDO::FETCH_COLUMN, 0);
    }

	public static function updateStatusId($dev_id, $status_id) {
		$connection = new PDO("sqlite:database/sqlite.db");

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

	public static function GetRainFallDeviceFromBasin($basin) {
        $connection = new PDO("sqlite:database/sqlite.db");
        $types  = "'VAISALA', 'Rain1', 'Rain2', 'Waterlevel & Rain 2', 'UAAWS', 'BSWM_Lufft', 'Davis'";

        $query = 'select v.* from v_devices v '.
            'where v.type in ('. $types .') AND v.basin = "'. $basin .'"'.
            'order by v.province, v.district, v.municipality ASC';

        return $connection->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function GetEnabledRainfallDeviceFromBasin($basin) {
        $connection = new PDO("sqlite:database/sqlite.db");
        $types  = "'VAISALA', 'Rain1', 'Rain2', 'Waterlevel & Rain 2', 'UAAWS', 'BSWM_Lufft', 'Davis'";

        $query = 'select v.dev_id from v_devices v '.
            'where v.type in ('. $types .') AND v.basin = "'. $basin .'" AND v.status <> "1"'.
            'order by v.province, v.district, v.municipality ASC';

        return $connection->query($query)->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public static function GetDisabledRainfallDeviceFromBasin($basin) {
        $connection = new PDO("sqlite:database/sqlite.db");
        $types  = "'VAISALA', 'Rain1', 'Rain2', 'Waterlevel & Rain 2', 'UAAWS', 'BSWM_Lufft', 'Davis'";

        $query = 'select v.dev_id from v_devices v '.
            'where v.type in ('. $types .') AND v.basin = "'. $basin .'" AND v.status == "1" '.
            'order by v.province, v.district, v.municipality ASC';

        return $connection->query($query)->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public static function GetWaterDeviceFromBasin($basin) {
        $connection = new PDO("sqlite:database/sqlite.db");
        $types ="'Waterlevel', 'Waterlevel & Rain 2'";

        $query = 'select v.*, riverindex from v_devices v '.
            'left outer join waterlevelinfo w on v.dev_id = w.devices_dev_id '.
            'where v.type in ('. $types .') AND v.basin = "'. $basin .'" '.
            'order by v.province, w.riverindex IS NULL, w.riverindex ASC';

        return $connection->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function GetEnabledWaterDeviceFromBasin($basin) {
        $connection = new PDO("sqlite:database/sqlite.db");
        $types ="'Waterlevel', 'Waterlevel & Rain 2'";

        $query = 'select v.dev_id, riverindex from v_devices v '.
            'left outer join waterlevelinfo w on v.dev_id = w.devices_dev_id '.
            'where v.type in ('. $types .') AND v.basin = "'. $basin .'" AND v.status <> "1" '.
            'order by v.province, w.riverindex IS NULL, w.riverindex ASC';

        return $connection->query($query)->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public static function GetDisabledWaterDeviceFromBasin($basin) {
        $connection = new PDO("sqlite:database/sqlite.db");
        $types ="'Waterlevel', 'Waterlevel & Rain 2'";

        $query = 'select v.dev_id, riverindex from v_devices v '.
            'left outer join waterlevelinfo w on v.dev_id = w.devices_dev_id '.
            'where v.type in ('. $types .') AND v.basin = "'. $basin .'" AND v.status == "1" '.
            'order by v.province, w.riverindex IS NULL, w.riverindex ASC';

        return $connection->query($query)->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public static function GetTempDeviceFromBasin($basin) {
        $connection = new PDO("sqlite:database/sqlite.db");
        $types  = "'VAISALA', 'UAAWS', 'BSWM_Lufft', 'Davis'";

        $query = 'select v.* from v_devices v '.
            'where v.type in ('. $types .') AND v.basin = "'. $basin .'"'.
            'order by v.province, v.district, v.municipality ASC';

        return $connection->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>