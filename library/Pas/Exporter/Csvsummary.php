<?php

/** The base class for using to export data as csv
 *
 * An example of use:
 *
 * <code>
 * <?php
 *
 * ?>
 * </code>
 * @author Daniel Pett <dpett at britishmuseum.org>
 * @copyright (c) 2014 Daniel Pett
 * @category Pas
 * @package Exporter
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero GPL v3.0
 * @version 1
 * @example
 */
class Pas_Exporter_Csvsummary extends Pas_Exporter_Generate
{

    /** The format
     * @access protected
     * @var string
     */
    protected $_format = 'csvsummary';

    /** The array of fields to use
     * @access protected
     * @var type
     */
    protected $_csvFields = array(
        'id', 'hoard', 'hoardID', 'broadperiod',
        'ruler', 'denomination', 'fromDate',
        'toDate','mint', 'geography',
        'quantity', 'created', 'createdBy',
        'updated', 'updatedBy'
    );

    /** Fetch results by page
     * @access public
     * @param integer $page
     * @return array
     */
    public function fetch($page)
    {
        $this->_params['page'] = $page;
        $this->_search->setFields($this->_csvFields);
        $this->_search->setParams($this->_params);
        $this->_search->execute();
        return $this->_search->processResults();
    }

    /** Create the csv
     * @access public
     */
    public function create()
    {
        $this->_search->setFields($this->_csvFields);
        $this->_search->setParams($this->_params);
        $this->_search->setCore('coinsummary');
        $this->_search->execute();
        $data = $this->_search->processResults();
        $paginator = $this->_search->createPagination();
        $pages = $paginator->getPages();
        $iterator = $pages->pageCount;
        $converter = new Pas_Exporter_ArrayToCsvHoard($this->_csvFields);
        $clean = $converter->convert($data);
        if ($iterator > 300) {
            set_time_limit(0);
            ini_set('memory_limit', '256M');
        }
        $file = fopen('php://temp/maxmemory:' . (12 * 1024 * 1024), 'r+');
        fputcsv($file, $this->_csvFields, ',', '"');
        foreach ($clean as $c) {
            fputcsv($file, array_values($c), ',', '"');
        }
        if ($iterator > 1) {
            foreach (range(2, $iterator) as $number) {
                $retrieved = $this->fetch($number);
                $record = $converter->convert($retrieved);
                foreach ($record as $rec) {
                    fputcsv($file, $rec, ',', '"');
                }
            }
        }
        rewind($file);
        $output = stream_get_contents($file);
        fclose($file);
        $filename = 'CsvCoinSummaryExportFor_' . $this->_user->username . '_' . $this->_dateTime . '.csv';
        $fc = Zend_Controller_Front::getInstance();
        $fc->getResponse()->setHeader('Content-type', 'text/csv; charset=utf-8');
        $fc->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $filename);
        echo $output;
    }
}