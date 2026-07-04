<?php

namespace Imedia\Main\Helpers\ActionFilter;

use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Engine\CurrentUser;

class Rights extends Base
{
    protected array $groups = [];
    protected array $operations = [];

    const ERROR_INVALID_GROUP = 'invalid_group';
    const ERROR_INVALID_OPERATIONS = 'invalid_operations';

    public function __construct(array $config = [])
    {
        if($config['groups']){

            $code = [];
            foreach($config['groups'] as $groupId){

                if(is_numeric($groupId)){
                    $this->groups[] = $groupId;
                } else {
                    $code[] = $groupId;
                }

            }

            if(!empty($code)){

                $query = GroupTable::getList(
                    [
                        'select' => ['ID'],
                        'filter' => ['=STRING_ID' => $code]
                    ]
                );
                while($row = $query->fetch()){
                    $this->groups[] = $row['ID'];
                }

            }

        }

        if($config['operations']){
            $this->operations = $config['operations'];
        }

        parent::__construct();
    }

    public function onBeforeAction(Event $event)
    {
        if(
            !empty($this->groups)
            || !empty($this->operations)
        ){

            $user = CurrentUser::get();

            if($user->isAdmin()){
                return null;
            }

            $groups = $user->getUserGroups();

            if(
                !empty($this->groups)
                && empty(array_intersect($this->groups, $groups))
            ){
                $this->addError(new Error('You don’t have permission to access', static::ERROR_INVALID_GROUP));
                return new EventResult(EventResult::ERROR, null, null, $this);
            }

            if(!empty($this->operations)){

                global $USER;

                $operations = $USER->GetAllOperations($groups);
                if(count(array_intersect(array_keys($this->operations), $operations)) !== count($this->operations)){
                    $this->addError(new Error('You don’t have permission to access', static::ERROR_INVALID_OPERATIONS));
                    return new EventResult(EventResult::ERROR, null, null, $this);
                }

            }

        }

        return null;
    }
}