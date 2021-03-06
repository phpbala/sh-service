<?php
# CLI command remove
require_once 'db.php';
class remove{
	public function confirm(){ ob_start();
		$message   =  "Are you sure want to remove permanently [y/N] :";
		print $message;
		flush();
		ob_flush();
		$confirmation  =  strtolower(trim( fgets( STDIN ) ));
		if ( $confirmation !== 'y' ) {
		   # Other keywords to exit
		   exit (0);
		} # if y means continue to next line access;
	}
	public function controller($fileName,$prompt=NULL){ $msg=BAD_FORMAT(); $c_dir = 'controller'; $file = $c_dir.'/'.ucfirst($fileName.'.php');
		if (file_exists($file)){
			if(is_dir($c_dir) && is_writable($c_dir) && is_readable($file)){  if($prompt!='y') remove::confirm();
		  		unlink($file);
		  		$msg = "\033[0;32m".ucfirst($c_dir).' '.ucfirst($fileName).' has been removed permanently'."\033[0m \n";
		    }else{
		  		$msg = "\033[0;31mPermission denied. coult't remove ".$c_dir."  \033[0m \n";
		    }
		} else {
		  $msg = "\033[0;31m".ucfirst($fileName)." ".$c_dir." does not exist.\033[0m \n";
		}
		return $msg;
	}
	public function model($fileName,$prompt=NULL){ $msg=BAD_FORMAT(); $c_dir = 'model'; $file = $c_dir.'/'.ucfirst($fileName.'.php');
		if (file_exists($file)){
			if(is_dir($c_dir) && is_writable($c_dir) && is_readable($file)){ if($prompt!='y') remove::confirm();
		  		unlink($file);
		  		$msg = "\033[0;32m".ucfirst($c_dir).' '.ucfirst($fileName).' has been removed permanently'."\033[0m \n";
		    }else{
		  		$msg = "\033[0;31mPermission denied. coult't remove ".$c_dir."  \033[0m \n";
		    }
		} else {
		  $msg = "\033[0;31m".ucfirst($fileName)." ".$c_dir." does not exist.\033[0m \n";
		}
		return $msg;
	}
	public function library($fileName,$prompt=NULL){ $msg=BAD_FORMAT(); $c_dir = 'library'; $file = $c_dir.'/'.ucfirst($fileName.'.php');
		if (file_exists($file)){
			if(is_dir($c_dir) && is_writable($c_dir) && is_readable($file)){ if($prompt!='y') remove::confirm();
		  		unlink($file);
		  		$msg = "\033[0;32m".ucfirst($c_dir).' '.ucfirst($fileName).' has been removed permanently'."\033[0m \n";
		    }else{
		  		$msg = "\033[0;31mPermission denied. coult't remove ".$c_dir."  \033[0m \n";
		    }
		} else {
		  $msg = "\033[0;31m".ucfirst($fileName)." ".$c_dir." does not exist.\033[0m \n";
		}
		return $msg;
	}
	public function extender($fileName,$prompt=NULL){ $msg=BAD_FORMAT(); $c_dir = 'extender'; $file = $c_dir.'/'.ucfirst($fileName.'.php');
		if (file_exists($file)){
			if(is_dir($c_dir) && is_writable($c_dir) && is_readable($file)){ if($prompt!='y') remove::confirm();
		  		unlink($file);
		  		$msg = "\033[0;32m".ucfirst($c_dir).' '.ucfirst($fileName).' has been removed permanently'."\033[0m \n";
		    }else{
		  		$msg = "\033[0;31mPermission denied. coult't remove ".$c_dir."  \033[0m \n";
		    }
		} else {
		  $msg = "\033[0;31m".ucfirst($fileName)." ".$c_dir." does not exist.\033[0m \n";
		}
		return $msg;
	}
	public function package($fileName,$prompt=NULL){ $msg=BAD_FORMAT(); $c_dir = 'package'; $file = $c_dir.'/'.ucfirst($fileName.'.php');
		if (file_exists($file)){
			if(is_dir($c_dir) && is_writable($c_dir) && is_readable($file)){ if($prompt!='y') remove::confirm();
		  		unlink($file);
		  		$msg = "\033[0;32m".ucfirst($c_dir).' '.ucfirst($fileName).' has been removed permanently'."\033[0m \n";
		    }else{
		  		$msg = "\033[0;31mPermission denied. coult't remove ".$c_dir."  \033[0m \n";
		    }
		} else {
		  $msg = "\033[0;31m".ucfirst($fileName)." ".$c_dir." does not exist.\033[0m \n";
		}
		return $msg;
	}
	public function module($fileName,$prompt=NULL){ $msg=BAD_FORMAT(); $c_dir = 'modules'; $file = $c_dir.'/'.strtolower($fileName);
		if (file_exists($file)){
			if(is_dir($c_dir) && is_writable($c_dir) && is_readable($file)){ if($prompt!='y') remove::confirm();
		  		rrmdir($file);
		  		$msg = "\033[0;32m".'Module '.strtolower($fileName).' has been removed permanently'."\033[0m \n";
		    }else{
		  		$msg = "\033[0;31mPermission denied. coult't remove ".$c_dir."  \033[0m \n";
		    }
		} else {
		  $msg = "\033[0;31m".strtolower($fileName)." module does not exist.\033[0m \n";
		}
		return $msg;
	}
	public function api($fileName,$dropTable=NULL,$prompt=NULL){ $msg=BAD_FORMAT(); $c_dir = 'extender/init'; $file = $c_dir.'/'.ucfirst($fileName.'.php');
		$dTc = strtolower($dropTable);
		if (file_exists($file)){
			if(is_dir($c_dir) && is_writable($c_dir) && is_readable($file)){ if($prompt!='y') remove::confirm();
		  		unlink($file);		  		
		  		if($dTc == '-t' || $dTc == '-table'){
		  			db()->query("DROP TABLE IF EXISTS `".strtolower($fileName)."`");
		  		}
		  		$msg = "\033[0;32m".'API '.ucfirst($fileName).' has been removed permanently'."\033[0m \n";
		    }else{
		  		$msg = "\033[0;31mPermission denied. coult't remove ".$c_dir."  \033[0m \n";
		    }
		} else {
  		  $msg = "\033[0;31m".ucfirst($fileName)." ".$c_dir." does not exist.\033[0m \n";
		  if($dTc == '-t' || $dTc == '-table'){
		  	if(db()->table_exists(strtolower($fileName))){
	  			db()->query("DROP TABLE IF EXISTS `".strtolower($fileName)."`");
	  			$msg = "\033[0;32m".'Table '.strtolower($fileName).' has been removed permanently'."\033[0m \n";
	  			$msg .= "\033[0;31m".ucfirst($fileName)." ".$c_dir." does not exist.\033[0m \n";
  			}
  		  }
  		}
  		return $msg;
  	}
   
}
