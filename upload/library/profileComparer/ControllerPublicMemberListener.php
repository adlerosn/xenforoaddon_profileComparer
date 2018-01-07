<?php

class profileComparer_ControllerPublicMemberListener {
	public static function callback($class, array &$extend){
		$baseClass = 'XenForo_ControllerPublic_Member';
		$toExtend = 'profileComparer_ControllerPublicMember';
		if($class==$baseClass && !in_array($toExtend, $extend)){
			$extend[]=$toExtend;
		}
	}
}
