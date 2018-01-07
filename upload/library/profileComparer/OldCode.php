<?php

class profileComparer_OldCode
{
	/*
	public function actionIndex(){
		$visitor = XenForo_Visitor::getInstance();
		if(!$visitor['user_id']){
			return $this->responseError(new XenForo_Phrase('requested_page_not_found'), 404);
		}
		$allowed=$visitor->hasPermission('general','warn');
		if(!$allowed) return $this->responseError(new XenForo_Phrase('requested_page_not_found'), 404);
		
		$toShow=false;
		try{
			$sub=explode('/',$this->_input->getInput()['_matchedRoutePath'])[1];
			if($sub=='show') $toShow=true;
		}catch(Exception $e){;};
		if($toShow) return $this->actionShow();
		
		$viewParams = array('user1'=>'','user2'=>'');
		try{
			$u1=$this->_input->getInput();
			$u1=explode('/',$u1['_matchedRoutePath'])[1];
			$userModel = XenForo_Model::create('XenForo_Model_User');
			$user = $userModel->getUserById($u1);
			$viewParams['user1']=$user['username'];
		}catch(Exception $e){;};
		
        // do stuff here to build $viewParams array and then...
        return $this->responseView(
            'XenForo_ViewPublic_Base',     // view name
            'kiror_profilecomparer_index', // content template name
            $viewParams//, //  data to be passed to content template
            //array('containerTemplate' =>
            //    'kiror_statistics_item') // your own container template
			);
    }
	
	public function actionShow(){
		$visitor = XenForo_Visitor::getInstance();
		if(!$visitor['user_id']){
			return $this->responseError(new XenForo_Phrase('requested_page_not_found'), 404);
		}
		$allowed=$visitor->hasPermission('general','warn');
		if(!$allowed) return $this->responseError(new XenForo_Phrase('requested_page_not_found'), 404);
		
		$viewParams = array('user1'=>array(),'user2'=>array(),'raw'=>'');
		try{
			$u1=$this->_input->getInput();
			$u1=explode('/',$u1['_matchedRoutePath'])[2];
			$u2=$this->_input->getInput();
			$u2=explode('/',$u2['_matchedRoutePath'])[3];
			$userModel = XenForo_Model::create('XenForo_Model_User');
			$user1 = $userModel->getUserByName($u1);
			$user2 = $userModel->getUserByName($u2);
			$viewParams['user1']=$user1;
			$viewParams['user2']=$user2;
		}catch(Exception $e){;};
		
		$viewParams['raw']=ProfileNdx_profilecomparer_actions::compare($viewParams['user1'],$viewParams['user2']);		
		
		//die(print_r($viewParams,true));
		//die(print_r($viewParams['user2'],true));
		
        return $this->responseView(
            'XenForo_ViewPublic_Base',     // view name
            'kiror_profilecomparer_show', // content template name
            $viewParams//, //  data to be passed to content template
            //array('containerTemplate' =>
            //    'kiror_statistics_item') // your own container template
			);
	}
	*/
	
	// https://en.wikipedia.org/wiki/Jaccard_index
	// https://upload.wikimedia.org/math/0/a/0/0a0633ce67c9130d890078a8d67f0474.png
	public static function jaccardIndex_core($a,$b){
		if($a==null || $b==null) return 0;
		if($a==$b) return 100;
		if(!is_array($a)) $a=str_split((string)$a);
		if(!is_array($b)) $b=str_split((string)$b);
		
		$a1=array();
		$b1=array();
		
		foreach($a as $i){
			foreach($a as $j){
				$a1[]=$i.'_'.$j;
			}
		}
		foreach($b as $i){
			foreach($b as $j){
				$b1[]=$i.'_'.$j;
			}
		}

		$inter=(array_intersect($a1, $b1));
		$union=(array_merge($a1, $b1));
		
		if (count($union)==0) return 0;
		return 100*count($inter)/count($union);
	}
	
	public static function jaccardIndex($a,$b){
		return number_format((float)self::jaccardIndex_core($a,$b),2,'.','').'%';
	}
	
	public static function compare($user1,$user2){
		$user1=(is_array($user1)?$user1:array());
		$user2=(is_array($user2)?$user2:array());
		$raw='
<style type="text/css">
.compareTable td
{
    padding:0 15px 0 15px;
}
</style>
		<table style="width:100%;" class="compareTable">
		<thead>
		<tr>
			<th><center>Criteria</center></th>
			<th><center>User 1</center></th>
			<th><center>User 2</center></th>
			<th><center>Similarity</center></th>
		</tr></thead><tbody>';
		//
		//array('email'=>'Email','register_date'=>'Joined','last_activity'=>'Last acticity');
		
		
		
		//throw new Exception('');
		
		$raw.='<tr>';
		$raw.='<td>'.'Avatar'.'</td>';
		$raw.='<td>';
			$raw.='<img src="'.XenForo_Template_Helper_Core::callHelper('avatar',array($user1,'m')).'" height="96" width="96">';
			$raw.='</td>';
		$raw.='<td>';
			$raw.='<img src="'.XenForo_Template_Helper_Core::callHelper('avatar',array($user2,'m')).'" height="96" width="96">';
			$raw.='</td>';
		$raw.='<td>???</td>';
		$raw.='</tr>'."\n";
		
		if(count($user1)==0 || count($user2)==0) return $raw.'</table>';
		//
		
		$v1='';
		if(array_key_exists('email',$user1)) $v1 = $user1['email'];
		$v2='';
		if(array_key_exists('email',$user2)) $v2 = $user2['email'];
		$raw.='<tr>';
		$raw.='<td>'.'Email'.'</td>';
		$raw.='<td>';
			$raw.=(strlen($v1)>0?htmlspecialchars($v1):'<i>none</i>');
			$raw.='</td>';
		$raw.='<td>';
			$raw.=(strlen($v2)>0?htmlspecialchars($v2):'<i>none</i>');
			$raw.='</td>';
		$raw.='<td>'.((strlen($v1)>0 && strlen($v2)>0) ? profileComparer_OldCode::jaccardIndex($user1['email'],$user2['email']) : '???').'</td>';
		$raw.='</tr>'."\n";
		//
		
		$cuf=self::getCustomUserFieldsArray();
		
		//$rp=ProfileNdx_indexer_shared::recoverFromDbNdx('usersProfiles');
		//die(print_r($cuf,true));
		//die(print_r($rp,true));
		
		$fp1=array();
		$fp2=array();
		
		//die(print_r($user1,true));
		
		$fp1=self::getFields($user1);
		$fp2=self::getFields($user2);
		
		/*
		try{$fp1=$rp[$user1['user_id']];
		}catch(Exception $e){;};
		try{$fp2=$rp[$user2['user_id']];
		}catch(Exception $e){;};
		*/
		
		$fp1=($fp1==null?array():$fp1);
		$fp2=($fp2==null?array():$fp2);
		
		$fields=array_merge(array_keys($fp1),array_keys($fp2));
		$fields=array_unique($fields);
		ksort($fields);
		foreach($fields as $f){
			if(!array_key_exists($f,$fp1)) $fp1[$f]=array();
			if(!array_key_exists($f,$fp2)) $fp2[$f]=array();
		}
		foreach($fields as $f){
			if(!isset($cuf[$f])) continue;
			$raw.='<tr>';
			$raw.='<td>'.$cuf[$f].'</td>';
			$disp1 = ((isset($fp1[$f]))?$fp1[$f]:null);
			$disp2 = ((isset($fp2[$f]))?$fp2[$f]:null);
			$raw.='<td>';
				$raw.=(count($disp1)>0?self::makeComparePreview(false,$disp1,$disp2,$cuf,$f):'<i>Absent</i>');
				$raw.='</td>';
			$raw.='<td>';
				$raw.=(count($disp2)>0?self::makeComparePreview(true,$disp1,$disp2,$cuf,$f):'<i>Absent</i>');
				$raw.='</td>';
			$raw.='<td>'.((count($disp1)>0 && count($disp2)>0) ? profileComparer_OldCode::jaccardIndex($disp1,$disp2) : '???').'</td>';
			$raw.='</tr>'."\n";
		}
		
		//die(print_r($fp1,true));
		
		//
		$raw.='</tbody></table>';
		//
		//die($raw);
		return $raw;
	}
	
	public static function startsWith($haystack, $needle)
	{//http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
		 $length = strlen($needle);
		 return (substr($haystack, 0, $length) === $needle);
	}
	
	public static function makeComparePreview($d,$d1,$d2,$cuf,$fld){
		$main  = null;
		$other = null;
		if(!$d){
			$main  = $d1;
			$other = $d2;
		}else{
			$other = $d1;
			$main  = $d2;
		}
		if(gettype($main)=='integer'){
			$main  = strval($main) ;
			$other = strval($other);
		}
		if(gettype($main)=='string'){
			$part = $main;
			if(array_key_exists($fld,$cuf) && array_key_exists($fld.'_choice_'.$part,$cuf)){
				$part = htmlspecialchars($cuf[$fld.'_choice_'.$part]);
			}else{
				$part = str_split($part, 30);
				if(count($part)>1){
					$part = htmlspecialchars($part[0]).'...';
				}else{
					$part = htmlspecialchars($part[0]);
				}
				if(strlen($part)==0){
					$part = '<span class="muted"><i>empty</i></span>';
				}
			}
			if($d1==$d2){
				$part = '<span class="backHighlight">'.$part.'</span>';
			}
			return $part;
		}
		else if(gettype($main)=='array'){
			$acc = '<ul>';
			foreach($main as $item){
				$part = $item;
				if(array_key_exists($fld,$cuf) && array_key_exists($fld.'_choice_'.$part,$cuf)){
					$part = htmlspecialchars($cuf[$fld.'_choice_'.$part]);
				}else{$part=htmlspecialchars($part);}
				if(in_array($item,$other)){
					$part = '<span class="backHighlight">'.$part.'</span>';
				}
				$part = '<li>'.$part.'</li>';
				$acc.=$part;
			}
			$acc.='</ul>';
			return $acc;
		}
		else{
			return '<span class="muted">Unknown data type</span>';
		}
	}
	
	public static function getCustomUserFieldsArray_noCache(){
		$dbc=XenForo_Application::get('db'); 
		$q = $dbc->fetchRow("SELECT `data_value`
							 FROM   `xf_data_registry`
							 WHERE  `data_key` = 'languages';");
		unset($dbc);
		$q=$q['data_value'];
		$oq=unserialize($q)[1]['phrase_cache'];
		
		$q=array('username'=>'User name',
				 'gender'=>'Gender',
				 'location'=>'Location',
				 'homepage'=>'Home Page',
				 'occupation'=>'Occupation',
				 'about'=>'About Me',
				 'signature'=>'Signature',
				 'dob_day'=>'DOB day',
				 'dob_month'=>'DOB month',
				 'dob_year'=>'DOB year',
				 'ips'=>'IPs');
		$k=array_keys($oq);
		for ($i=0;$i<count($k);$i++)
		{
			if(self::startsWith($k[$i],'user_field_'))
			{
				$str=$k[$i];
				$len=count($str);
				$str=substr($str,11);
				$q[$str]=$oq['user_field_'.$str];
			}
		}
		unset($k);
		unset($oq);
		
		return $q;
	}
	protected static $_cache_getCustomUserFieldsArray = null;
	public static function getCustomUserFieldsArray(){
		if(is_null(self::$_cache_getCustomUserFieldsArray))
			self::$_cache_getCustomUserFieldsArray = self::getCustomUserFieldsArray_noCache();
		return self::$_cache_getCustomUserFieldsArray;
	}
	
	public static function getFields($xfuser){
		$user = [];
		$user['username']=$xfuser['username'];
		$user['gender']=$xfuser['gender'];
		$user['location']=$xfuser['location'];
		$user['homepage']=$xfuser['homepage'];
		$user['occupation']=$xfuser['occupation'];
		$user['about']=$xfuser['about'];
		$user['signature']=$xfuser['signature'];
		$user['dob_day']=$xfuser['dob_day'];
		$user['dob_month']=$xfuser['dob_month'];
		$user['dob_year']=$xfuser['dob_year'];
		if(array_key_exists('custom_fields',$xfuser)) {
			$tcuf = unserialize($xfuser['custom_fields']);
			if (is_array($tcuf)){
				foreach(array_keys($tcuf) as $key=>$val){
					$user[$val]=$tcuf[$val];
				}
			}
		}
		$user['ips']=$xfuser['ips'];
		return $user;
	}
}
