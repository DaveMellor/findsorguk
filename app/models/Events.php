<?php
/** Model for manipulating events data
* @category 	Pas
* @package 	Pas_Db_Table
* @subpackage 	Abstract
* @author 	Daniel Pett dpett @ britishmuseum.org
* @copyright 	2010 - DEJ Pett
* @license 	GNU General Public License
* @version 	1
* @since 	22 September 2011
*/

class Events extends Pas_Db_Table_Abstract {

    /** Name of table
     * @access protected
     * @var string
     */
    protected $_name = 'events';

    /** The primary key
     * @access protected
     * @var int
     */
    protected $_primary = 'id';

    /** The higherlevel array
     * @access public
     * @var array
     */
    protected $higherlevel = array('flos', 'fa');
    
    /**
    * Retrieves all event data by id number
    * @param integer $id
    * @return array
    */
    public function getEventData($id){
        $select = $this->select()
                ->from($this->_name)
                ->joinLeft('users','users.id = events.createdBy',
                        array('fullname','email'))
                ->joinLeft('staffregions','events.eventRegion = staffregions.id',
                        array('location' => 'description'))
                ->joinLeft('eventtypes',$this->_name . '.eventType = eventtypes.id',
                        array('type'))
                ->where('events.id = ?',(int)$id);
        return $this->getAdapter()->fetchAll($select);
    }

    /**
    * Retrieves all event datae by id number
    * @param int $page
    * @return array
    */
    public function getEventsAdmin($page) {
        $events = $this->getAdapter();
        $select = $events->select()
                ->from($this->_name)
                ->joinLeft('staffregions','events.eventRegion = staffregions.id',
                        array('rID' => 'ID','description'))
                ->joinLeft('users','users.id = ' . $this->_name . '.createdBy',
                        array('fullname'))
                ->joinLeft('users','users_2.id = ' . $this->_name . '.updatedBy',
                        array('fn' => 'fullname'))
                ->order('eventStartDate DESC');
        if(in_array($this->getUserRole(),$this->higherlevel)) {
            $select->where($this->_name . '.createdBy = ?', $this->userNumber());
        }
        $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage(30)
                ->setPageRange(10);
        if(isset($page) && ($page != "")) {
            $paginator->setCurrentPageNumber($page);
        }
        return $paginator;
    }

    /**
    * Retrieves all event datae by id number
    * @param integer $page
    * @return array
    */
    public function getUpcomingEvents(){
        $select = $this->select()
                ->from($this->_name,array(
                    'eventStartDate', 'eventTitle', 'organisation'
                    ))
                ->where('events.eventStartDate >= ?',
                        Zend_Date::now()->toString('yyyy-MM-dd'))
                ->joinLeft('staffregions','events.eventRegion = staffregions.id',
                        array('location' => 'description'))
                ->joinLeft('eventtypes',$this->_name . '.eventType = eventtypes.id',
                        array('type'))
                ->joinLeft('users','events.createdBy = users.id',
                        array('fullname','i' => 'id','email'))
                ->order('eventStartDate DESC');
        return $this->getAdapter()->fetchAll($select);
    }

    /**
    * Retrieves all events data in the archive
    * @param array $params
    * @return array
    */
    public function getArchivedEventsList($params) {
        $events = $this->getAdapter();
        $select = $events->select()->from($this->_name)
            ->joinLeft('users','events.createdBy = users.id',
                    array('fullname','i' => 'id','email'))
            ->joinLeft('staffregions','events.eventRegion = staffregions.id',
                    array('location'=>'description'))
            ->joinLeft('eventtypes',$this->_name.'.eventType = eventtypes.id',
                    array('type'))
            ->where('events.eventStartDate <= ?',Zend_Date::now()->toString('yyyy-MM-dd'))
            ->order('eventStartDate DESC');
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(20)
                        ->setPageRange(10);
    if(isset($params['page']) && ($params['page'] != "")) {
            $paginator->setCurrentPageNumber($params['page']);
    }
    return $paginator;
    }
    
    /**
    * Retrieves all event data and sends data for mapping
    * @param integer $page
    * @return array
    */
    public function getMapData() {
        $select = $this->select()
                ->from($this->_name, array(
                    'id', 'name' => 'eventTitle', 'df' => 'DATE_FORMAT(eventStartDate,"%D %M %Y")',
                    'dt' => 'DATE_FORMAT(eventEndDate,"%D %M %Y")','lat' => 'latitude',
                    'lng' => 'longitude'))
                ->where('latitude IS NOT NULL')
                ->where('events.eventStartDate >= ?',
                        Zend_Date::now()->toString('yyyy-MM-dd') . ' 00:00:00');
        return $this->getAdapter()->fetchAll($select);
    }
}
