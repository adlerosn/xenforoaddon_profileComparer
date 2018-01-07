<?php

class profileComparer_OldCodeBridge{
	public static function compare($comparing){
		$preppend = '';
		if(!isset($comparing[0])||!isset($comparing[1])){
			$preppend.= '<i>Select two users to compare</i><br />';
		}
		$u1 = ((isset($comparing[0]))?$comparing[0]:[]);
		$u2 = ((isset($comparing[1]))?$comparing[1]:[]);
		return profileComparer_OldCode::compare($u1,$u2);
	}
}
