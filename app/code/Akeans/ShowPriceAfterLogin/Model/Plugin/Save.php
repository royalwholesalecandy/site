<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\ShowPriceAfterLogin\Model\Plugin;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;

class Save {

  protected $_filterBuilder;
  protected $_groupFactory;
  protected $_groupRepository;
  protected $_searchCriteriaBuilder;

  public function __construct(FilterBuilder $filterBuilder,GroupRepositoryInterface $groupRepository, SearchCriteriaBuilder $searchCriteriaBuilder, GroupFactory $groupFactory)
  {
    $this->_filterBuilder = $filterBuilder;
    $this->_groupRepository = $groupRepository;
    $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    $this->_groupFactory = $groupFactory;
  }       

  public function afterexecute(\Magento\Customer\Controller\Adminhtml\Group\Save $save, $result)
  {   
    $order_prefix = $save->getRequest()->getParam('order_prefix');
    $code = $save->getRequest()->getParam('code'); 
	//  print_r($save->getRequest()->getParams());die;
	//  echo $code.'test';die;
    if(empty($code))
      $code = 'NOT LOGGED IN';
    $_filter = [ $this->_filterBuilder->setField('main_table.customer_group_code')->setConditionType('eq')->setValue($code)->create() ];
    $customerGroups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->addFilters($_filter)->create())->getItems();
    $customerGroup = array_shift($customerGroups);
    if($customerGroup){
     $group = $this->_groupFactory->create();
     $group->load($customerGroup->getId());
     $group->setCode($customerGroup->getCode());
     $group->setData('order_prefix',$order_prefix);
     $group->save();
    }
    return $result;
  }       
}
