<?php
/********************************************************************************* 
 *  This file is part of Sentrifugo.
 *  Copyright (C) 2014 Sapplica
 *   
 *  Sentrifugo is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Sentrifugo is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Sentrifugo.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Sentrifugo Support <support@sentrifugo.com>
 ********************************************************************************/

class Default_LanguageController extends Zend_Controller_Action
{

    private $options;
	public function preDispatch()
	{
		 
		
	}
	
    public function init()
    {
        $this->_options= $this->getInvokeArg('bootstrap')->getOptions();
		
    }

    public function indexAction()
    {
		$languagemodel = new Default_Model_Language();	
        $call = $this->_getParam('call');
		if($call == 'ajaxcall')
				$this->_helper->layout->disableLayout();
		
		$view = Zend_Layout::getMvcInstance()->getView();		
		$objname = $this->_getParam('objname');
		$refresh = $this->_getParam('refresh');
		$dashboardcall = $this->_getParam('dashboardcall',null);
		$data = array();	$searchQuery = '';	$searchArray = array();	$tablecontent='';
		
		if($refresh == 'refresh')
		{
			if($dashboardcall == 'Yes')
				$perPage = DASHBOARD_PERPAGE;
			else	
				$perPage = PERPAGE;
										
			$sort = 'DESC';$by = 'modifieddate';$pageNo = 1;$searchData = '';$searchQuery = '';	
			$searchArray = array();
			//$sort = 'DESC';$by = 'modifieddate';$perPage = 10;$pageNo = 1;$searchData = '';
		}
		else 
		{
			$sort = ($this->_getParam('sort') !='')? $this->_getParam('sort'):'DESC';
			$by = ($this->_getParam('by')!='')? $this->_getParam('by'):'modifieddate';
			if($dashboardcall == 'Yes')
				$perPage = $this->_getParam('per_page',DASHBOARD_PERPAGE);
			else 
				$perPage = $this->_getParam('per_page',PERPAGE);
			
			$pageNo = $this->_getParam('page', 1);
			$searchData = $this->_getParam('searchData');	
			$searchData = rtrim($searchData,',');
		}
		$dataTmp = $languagemodel->getGrid($sort,$by,$perPage,$pageNo,$searchData,$call,$dashboardcall);		
		
		array_push($data,$dataTmp);
		$this->view->dataArray = $data;
		$this->view->call = $call ;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    public function viewAction()
	{	
		$id = $this->getRequest()->getParam('id');
		$callval = $this->getRequest()->getParam('call');
		if($callval == 'ajaxcall')
			$this->_helper->layout->disableLayout();
		$objName = 'language';
		$languageform = new Default_Form_language();
		$languageform->removeElement("submit");
		$elements = $languageform->getElements();
		if(count($elements)>0)
		{
			foreach($elements as $key=>$element)
			{
				if(($key!="Cancel")&&($key!="Edit")&&($key!="Delete")&&($key!="Attachments")){
				$element->setAttrib("disabled", "disabled");
					}
        	}
        }
		$languagemodel = new Default_Model_Language();
        try
		{
			if($id)
			{
				if(is_numeric($id) && $id>0)
				{		
					$data = $languagemodel->getLanguageDataByID($id);
					if(!empty($data))
					{
						$languageform->populate($data[0]);
						$this->view->controllername = $objName;
						$this->view->id = $id;
						$this->view->form = $languageform;
						$this->view->ermsg = '';
					}
                    else
					{
						$this->view->ermsg = 'norecord';
					}					
				}
				else
				{
					$this->view->ermsg = 'norecord';
				}
            }
			else
			{
				$this->view->ermsg = 'norecord';
			}
        }catch(Exception $e)
		{
			 $this->view->ermsg = 'nodata';
		} 			
	}
	
	
	public function editAction()
	{	
	    $auth = Zend_Auth::getInstance();
     	if($auth->hasIdentity()){
					$loginUserId = $auth->getStorage()->read()->id;
		}
		$id = $this->getRequest()->getParam('id');
		$callval = $this->getRequest()->getParam('call');
		if($callval == 'ajaxcall')
			$this->_helper->layout->disableLayout();
		
		$languageform = new Default_Form_language();		
		$languagemodel = new Default_Model_Language();
		try
		{
			if($id)
			{
				if(is_numeric($id) && $id>0)
				{
					$data = $languagemodel->getLanguageDataByID($id);
					if(!empty($data))
					  {
					    $languageform->populate($data[0]);
						$languageform->submit->setLabel('Update'); 
						$this->view->ermsg = '';
					  }
					else  
					  $this->view->ermsg = 'norecord';
		        }  
				else
				{
					$this->view->ermsg = 'norecord';
				}
			}
			else
			{
				$this->view->ermsg = '';
			}
        }
        catch(Exception $e)
		{
			 $this->view->ermsg = 'nodata';
		} 		
		$this->view->form = $languageform;
		if($this->getRequest()->getPost()){
		    if($languageform->isValid($this->_request->getPost())){
			    $id = $this->_request->getParam('id'); 
			    $languagename = $this->_request->getParam('languagename');
				$description = $this->_request->getParam('description');
				$date = new Zend_Date();
				$menumodel = new Default_Model_Menu();
				$actionflag = '';
				$tableid  = ''; 
				   $data = array( 'languagename'=>trim($languagename),
				      			 'description'=>trim($description),
								  'modifiedby'=>$loginUserId,
								  //'modifieddate'=>$date->get('yyyy-MM-dd HH:mm:ss')
								  'modifieddate'=>gmdate("Y-m-d H:i:s")
						);
					if($id!=''){
						$where = array('id=?'=>$id);  
						$actionflag = 2;
					}
					else
					{
					    $data['createdby'] = $loginUserId;
						//$data['createddate'] = $date->get('yyyy-MM-dd HH:mm:ss');
						$data['createddate'] = gmdate("Y-m-d H:i:s");
						$data['isactive'] = 1;
						$where = '';
						$actionflag = 1;
					}
					//echo "<pre>";print_r($data);exit;
					$Id = $languagemodel->SaveorUpdateLanguageData($data, $where);
					if($Id == 'update')
					{
					   $tableid = $id;
					   $this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Language updated successfully."));
					}   
					else
					{
                       $tableid = $Id; 	
                        $this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Language added successfully."));					   
					}   
					$menuidArr = $menumodel->getMenuObjID('/language');
					$menuID = $menuidArr[0]['id'];
					//echo "<pre>";print_r($menuidArr);exit;
					$result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$tableid);
					//echo $result;exit;
    			    $this->_redirect('language');		
			}else
			{
     			$messages = $languageform->getMessages();
				foreach ($messages as $key => $val)
					{
						foreach($val as $key2 => $val2)
						 {
							//echo $key." >> ".$val2;
							$msgarray[$key] = $val2;
							break;
						 }
					}
				$this->view->msgarray = $msgarray;
			
			}
		}
	}
	
	public function deleteAction()
	{
	     $auth = Zend_Auth::getInstance();
     		if($auth->hasIdentity()){
					$loginUserId = $auth->getStorage()->read()->id;
				}
		 $id = $this->_request->getParam('objid');
		 $messages['message'] = '';
		 $messages['msgtype'] = '';
		 $actionflag = 3;
		    if($id)
			{
			$languagemodel = new Default_Model_Language();
			  $menumodel = new Default_Model_Menu();
			  $data = array('isactive'=>0,'modifieddate'=>gmdate("Y-m-d H:i:s"));
			  $where = array('id=?'=>$id);
                          $language_data = $languagemodel->getsingleLanguageData($id);
                         // print_r($language_data);exit;
			  $Id = $languagemodel->SaveorUpdateLanguageData($data, $where);
			    if($Id == 'update')
                            {
                                sapp_Global::send_configuration_mail("Language", $language_data['languagename']);
				   $menuidArr = $menumodel->getMenuObjID('/language');
				   $menuID = $menuidArr[0]['id'];
					//echo "<pre>";print_r($objid);exit;
				   $result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$id); 
				   $messages['message'] = 'Language deleted successfully.';
				   $messages['msgtype'] = 'success';
				}   
				else
				{
                   $messages['message'] = 'Language cannot be deleted.';				
				   $messages['msgtype'] = 'error';
				}   
			}
			else
			{ 
			 $messages['message'] = 'Language cannot be deleted.';
			 $messages['msgtype'] = 'error';
			}
			$this->_helper->json($messages);
		
	}
	
	public function addpopupAction()
	{
		Zend_Layout::getMvcInstance()->setLayoutPath(APPLICATION_PATH."/layouts/scripts/popup/");
		$auth = Zend_Auth::getInstance();
     	if($auth->hasIdentity()){
					$loginUserId = $auth->getStorage()->read()->id;
		}
		$id = $this->getRequest()->getParam('id');
		
		$languageform = new Default_Form_language();		
		$languagemodel = new Default_Model_Language();
		$languageform->setAction(DOMAIN.'language/addpopup');		
		
		$controllername = 'language';
		if($this->getRequest()->getPost())
		{
		    if($languageform->isValid($this->_request->getPost()))
			{
			    $id = $this->_request->getParam('id'); 
			    $languagename = $this->_request->getParam('languagename');
				$description = $this->_request->getParam('description');
				$date = new Zend_Date();
				$menumodel = new Default_Model_Menu();
				$actionflag = '';
				$tableid  = ''; 
				   $data = array( 'languagename'=>trim($languagename),
				      			 'description'=>trim($description),
								  'modifiedby'=>$loginUserId,
								  //'modifieddate'=>$date->get('yyyy-MM-dd HH:mm:ss')
								  'modifieddate'=>gmdate("Y-m-d H:i:s")
						);
					if($id!=''){
						$where = array('id=?'=>$id);  
						$actionflag = 2;
					}
					else
					{
					    $data['createdby'] = $loginUserId;
						//$data['createddate'] = $date->get('yyyy-MM-dd HH:mm:ss');
						$data['createddate'] = gmdate("Y-m-d H:i:s");
						$data['isactive'] = 1;
						$where = '';
						$actionflag = 1;
					}
					//echo "<pre>";print_r($data);exit;
					$Id = $languagemodel->SaveorUpdateLanguageData($data, $where);
					$tableid = $Id; 	
					
					$menuidArr = $menumodel->getMenuObjID('/language');
					$menuID = $menuidArr[0]['id'];
					$result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$tableid);
					
					$languageData = $languagemodel->fetchAll('isactive = 1','languagename')->toArray();
					$opt ='';
					foreach($languageData as $record){
						$opt .= sapp_Global::selectOptionBuilder($record['id'], $record['languagename']);
					}
					$this->view->languageData = $opt;
    			    $this->view->eventact = 'added';
					$close = 'close';
					$this->view->popup=$close;
			}else
			{
     			$messages = $languageform->getMessages();
				foreach ($messages as $key => $val)
					{
						foreach($val as $key2 => $val2)
						 {
							//echo $key." >> ".$val2;
							$msgarray[$key] = $val2;
							break;
						 }
					}
				$this->view->msgarray = $msgarray;
			
			}
		}
		$this->view->form = $languageform;
		$this->view->controllername = $controllername;
	}

}
