<?php

/**
 * Class implmenting metadata output for People's Network Discovery Service
 * @category Pas
 * @package Pas_OaiPmhRepository
 * @subpackage Metadata Formats
 * @since 6/2/12
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @version 1
 * @copyright (c) 2014 Daniel Pett
 * @author Daniel Pett <dpett at britishmuseum.org>
 */
class Pas_OaiPmhRepository_Metadata_PndsDc extends Pas_OaiPmhRepository_Metadata_Abstract
{

    /** Create the institution
     * @todo perhaps move out to another class
     * @param type $inst
     * @return string
     */
    public function institution($inst)
    {
        if (!is_null($inst)) {
            $institutions = new Institutions();
            $where = array();
            $where[] = $institutions->getAdapter()->quoteInto('institution = ?', $inst);
            $institution = $institutions->fetchRow($where);
            if (!is_null($institution)) {
                return $institution->description;
            }
        } else {
            return 'The Portable Antiquities Scheme';
        }
    }

    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'pnds_dc';

    /** OAI namespace */
    const OAI_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/static-repository';

    /** XML namespace for output format */
    const METADATA_NAMESPACE = 'http://purl.org/mla/pnds/pndsdc/';

    /** XML schema for output format */
    const METADATA_SCHEMA = 'http://www.ukoln.ac.uk/metadata/pns/pndsdcxml/2005-06-13/xmls/pndsdc.xsd';

    /** XML namespace for unqualified Dublin Core */
    const DC_NAMESPACE_URI = 'http://purl.org/dc/elements/1.1/';

    /** DC terms namespace */
    const DC_TERMS_NAMESPACE = 'http://purl.org/dc/terms/';

    /** PNDS namespace */
    const PNDS_TERMS_NAMESPACE = 'http://purl.org/mla/pnds/terms/';

    /** Image extension */
    const IMAGE_EXTENSION = '.jpg';

    /**
     * Appends Dublin Core metadata.
     * Appends a metadata element, an child element with the required format,
     * and further children for each of the Dublin Core fields present in the
     * item.
     * @access protected
     */
    protected $_view;

    /** Init the class
     * @access public
     * @return \Zend_View
     */
    public function init()
    {
        $this->_view = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer')->view;
    }

    /** Append metadata
     * @access public
     * @return void
     */
    public function appendMetadata()
    {
        $metadataElement = $this->document->createElement('metadata');
        $this->parentElement->appendChild($metadataElement);

        $pnds = $this->document->createElementNS(
            self::METADATA_NAMESPACE, 'pndsdc:description');
        $metadataElement->appendChild($pnds);

        /* Must manually specify XML schema uri per spec, but DOM won't include
            * a redundant xmlns:xsi attribute, so we just set the attribute
            */
        $pnds->setAttribute('xmlns', self::OAI_NAMESPACE);
        $pnds->setAttribute('xmlns:dc', self::DC_NAMESPACE_URI);
        $pnds->setAttribute('xmlns:dcterms', self::DC_TERMS_NAMESPACE);
        $pnds->setAttribute('xmlns:xsi', parent::XML_SCHEMA_NAMESPACE_URI);
        $pnds->setAttribute('xmlns:pndsterms', self::PNDS_TERMS_NAMESPACE);
        $pnds->setAttribute(
            'xsi:schemaLocation', self::METADATA_NAMESPACE . ' ' . self::METADATA_SCHEMA);

        if (!array_key_exists('0', $this->item)) {

            if (array_key_exists('objecttype', $this->item) && $this->item['objecttype'] === 'HOARD') {
                $uri = self::HOARD_URI;
            } else {
                $uri = self::RECORD_URI;
            }

            if(!array_key_exists('creator', $this->item)){
                $this->item['creator'] = self::SOURCE;
            }

            if(!array_key_exists('broadperiod', $this->item)){
                $this->item['broadperiod'] = 'UNKNOWN';
            }

            if (array_key_exists('description', $this->item)) {
                $description = strip_tags(str_replace(array("\n", "\r", '    '),
                    array('', '', ' '), $this->item['description']));
            } else {
                $description = 'No description available';
            }

            $data = array(
                'identifier' => $this->_serverUrl . $uri. $this->item['id'],
                'title' => $this->item['broadperiod'] . ' ' . $this->item['objecttype'],
                'description' => strtr($description, array('\x0B' => '&#x0B;')),
                'subject' => self::SUBJECT,
                'type' => $this->item['objecttype']
            );


            foreach ($data as $k => $v) {
                $this->appendNewElement($pnds, 'dc:' . $k, $v);
            }

            $rightsURI = $this->appendNewElement($pnds, 'dcterms:license', '');
            $rightsURI->setAttribute('valueURI', self::LICENSE_URI);
            $this->appendNewElement($pnds, 'dcterms:rightsHolder', self::RIGHTS_HOLDER);
            $data2 = array(
                'creator' => $this->item['creator'],
                'contributor' => $this->item['institution'],
                'publisher' => self::RIGHTS_HOLDER,
                'language' => self::LANGUAGE,
                'format' => self::FORMAT,
            );

            foreach ($data2 as $k => $v) {
                $this->appendNewElement($pnds, 'dc:' . $k, $v);
            }

            if(!array_key_exists('county', $this->item)) {
                $this->item['county'] = NULL;
            }

            if(!array_key_exists('district', $this->item)) {
                $this->item['district'] = NULL;
            }

            $spatial = array(
                'county' => $this->item['county'],
                'district' => $this->item['district']
            );

            if(!array_key_exists('fromdate', $this->item)) {
                $this->item['fromdate'] = NULL;
            }

            if(!array_key_exists('todate', $this->item)) {
                $this->item['todate'] = NULL;
            }

            $temporal = array(
                    'year1' => $this->item['fromdate'],
                    'year2' => $this->item['todate'],
                );

            //Check for availability of NGR and therefore latlon conversions
            if (array_key_exists('knownas', $this->item) && array_key_exists('fourFigure', $this->item))
            {
                $lat = $this->item['fourFigureLat'];
                $lon = $this->item['fourFigureLon'];
                $spatial['coords'] = $lat . ',' . $lon;
            }
            if(array_filter($spatial)) {
                foreach ($spatial as $k => $v) {
                    $this->appendNewElement($pnds, 'dcterms:spatial', $v);
                }
            }
            if(array_filter($temporal)) {
                foreach ($temporal as $k => $v) {
                    $this->appendNewElement($pnds, 'dcterms:temporal', $v);
                }
            }

            if (array_key_exists('thumbnail', $this->item)) {
                $thumbnail = $this->_serverUrl . self::THUMB_PATH . $this->item['thumbnail'] . self::IMAGE_EXTENSION;
                $this->appendNewElement($pnds, 'pndsterms:thumbnail', $thumbnail);
            }
        }
    }


    /**
     * Returns the OAI-PMH metadata prefix for the output format.
     * @access public
     * @return string Metadata prefix
     */
    public function getMetadataPrefix()
    {
        return self::METADATA_PREFIX;
    }

    /**
     * Returns the XML schema for the output format.
     * @access public
     * @return string XML schema URI
     */
    public function getMetadataSchema()
    {
        return self::METADATA_SCHEMA;
    }

    /**
     * Returns the XML namespace for the output format.
     * @access public
     * @return string XML namespace URI
     */
    public function getMetadataNamespace()
    {
        return self::METADATA_NAMESPACE;
    }
}