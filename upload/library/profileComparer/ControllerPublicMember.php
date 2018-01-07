<?php

class profileComparer_ControllerPublicMember extends XFCP_profileComparer_ControllerPublicMember{
	public function actionCompare(){
		$visitor = XenForo_Visitor::getInstance();
		if(!$visitor->hasPermission('general','compare_profiles'))
			return $this->responseError(new XenForo_Phrase('requested_page_not_found'), 404);
		$userModel = $this->_getUserModel();
		$userId = $this->_input->filterSingle('user_id', XenForo_Input::ARRAY_SIMPLE);
		$userIdU = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		if($userIdU && count($userId)==0) $userId[]=$userIdU;
		$username = $this->_input->filterSingle('username', XenForo_Input::STRING);
		if ($username !== ''){
			$user = $userModel->getUserByName($username);
			if ($user){
				$lnkPar = [];
				$i = 0;
				foreach($userId as $uid){
					if($user['user_id']!=$uid){
						$lnkPar['user_id['.$i.']']=$uid;
						$i++;
					}
				};
				$lnkPar['user_id['.$i.']']=$user['user_id'];
				return $this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildPublicLink('members/compare', '',$lnkPar)
				);
			}
		}
		$users_2 = $userModel->getUsersByIds($userId,['join'=>XenForo_Model_User::FETCH_LAST_ACTIVITY|XenForo_Model_User::FETCH_USER_PROFILE]);
		$users =[];
		foreach($userId as $uid){
			$users[$uid] = $users_2[$uid];
		}
		unset($users_2);
		$users = array_slice($users,-2,2,true);
		$ipModel = XenForo_Model::create('XenForo_Model_Ip');
		$guest = [
			'user_id'=>0,
			'username'=>'guest',
			'custom_title'=>'',
			'avatar_date'=>0,
			'avatar_width'=>0,
			'avatar_height'=>0,
		];
		foreach($users as $k => $user){
			$ips = [];
			$ips2 = $ipModel->getIpsByUserId($user['user_id']);
			foreach($ips2 as $ip)
				if(!in_array($ip,$ips))
					$ips[]=$ip;
			$users[$k]['ips'] = $ips;
		}
		$uc = [];
		$i = 0;
		foreach($users as $k => $user){
			$uc[]=$user;
			$i++;
		}
		unset($i);
		$comparations = profileComparer_OldCodeBridge::compare($uc);
		$viewParams = [
			'comparing'=>$users,
			'guest'=>$guest,
			'comparations'=>$comparations,
		];
		return $this->responseView('XenForo_ViewPublic_Base','member_compare',$viewParams);
	}
}
