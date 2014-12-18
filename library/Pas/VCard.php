<?php
/** Class for creating vcard for contacts
 * An example of use:
 *
 * <code>
 * <?php
 * $card = new Pas_VCard();
 * $card->setData($data);
 * $card->createCard();
 * </code>
 *
 * @author Daniel Pett <dpett at britishmuseum.org>
 * @copyright (c) 2014, Daniel Pett
 * @category Pas
 * @package VCard
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero GPL v3.0
 * @example /app/modules/contacts/views/scripts/coroners/profile.vcf.phtml
 */
class Pas_VCard
{
    /** The array of data to parse to form card
     * @access protected
     * @var null
     */
    protected $_data;

    /** The default class of card
     * @access protected
     * @var string
     */
    protected $_class = 'PUBLIC';

    /** The array of classes
     * @access protected
     * @var array
     */
    protected $_classes = array('PUBLIC', 'PRIVATE', 'CONFIDENTIAL');

    /** The revision date
     * @access protected
     * @var
     */
    protected $_revisionDate;

    /** Get the data to parse
     * @access public
     * @return mixed
     */
    public function getData()
    {
        return $this->_data;
    }

    /** Set the data to parse
     * @param mixed $data
     * @access public
     */
    public function setData(array $data)
    {
        $this->_data = $data;
        return $this;
    }

    /** Get the class of the vcard
     * @access public
     * @return mixed
     */
    public function getClass()
    {
        return $this->_class;
    }

    /** Set the class of the vcard
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->_class = $class;
        return $this;
    }

    /** Get the classes
     * @access public
     * @return array
     */
    public function getClasses()
    {
        return $this->_classes;
    }


    /** Get the revision date if set
     * @return mixed
     * @access public
     */
    public function getRevisionDate()
    {
        return $this->_revisionDate;
    }

    /** Set the revision date
     * @access public
     * @param mixed $revision_date
     */
    public function setRevisionDate($revisionDate)
    {
        $this->_revisionDate = $revisionDate;
        return $this;
    }

    /** The class constructor.
     * @access public
     * @return \Pas_VCard
     */
    public function vCard()
    {
        return $this;
    }

    /** Build the card contents
     * @access public
     * @param  array $data
     * @return string
     */
    public function buildCard(array $data)
    {
        if (!array_key_exists('display_name', $data)) {
            $data['display_name'] = trim($data['first_name'] . ' ' . $data['last_name']);
        }
        if (!array_key_exists('sort_string', $data)) {
            $data['sort_string'] = $data['last_name'];
        }
        if (!array_key_exists('sort_string', $data)) {
            $data['sort_string'] = $data['company'];
        }
        if (!array_key_exists('timezone', $data)) {
            $data['timezone'] = date('O');
        }
        if (is_null($this->getRevisionDate())) {
            $revisionDate = date('Y-m-d H:i:s');
        }

        $card = 'BEGIN:VCARD' . PHP_EOL;
        $card .= 'VERSION:3.0' . PHP_EOL;
        $card .= 'CLASS:' . $this->getClass() . PHP_EOL;
        $card .= 'PRODID:-//Contact vcard generated by finds.org.uk//NONSGML Version 1//EN' . PHP_EOL;
        $card .= 'REV:' . $revisionDate . PHP_EOL;
        if (array_key_exists('display_name', $data)) {
            $card .= 'FN:' . $data['display_name'] . PHP_EOL;
        }
        $card .= 'N:';
        if(array_key_exists('last_name', $data)) {
            $card .= $data['last_name'] . ';';
        }
        if(array_key_exists('first_name', $data)) {
            $card .= $data['first_name'] . ';';
        }
        if(array_key_exists('additional_name', $data)) {
            $card .= $data['additional_name'] . ';';
        }
        if(array_key_exists('name_prefix', $data)) {
            $card .= $data['name_prefix'] . ';';
        }
        if(array_key_exists('name_suffix', $data)) {
            $card .= $data['name_suffix'];
        }
        $card .= PHP_EOL;

        if (array_key_exists('nickname', $data)) {
            $card .= 'NICKNAME:' . $data['nickname'] . PHP_EOL;
        }
        if (array_key_exists('title', $data)) {
            $card .= 'TITLE:' . $data['title'] . PHP_EOL;
        }
        if (array_key_exists('company', $data)) {
            $card .= 'ORG:' . $data['company'];
        }
        if (array_key_exists('department', $data)) {
            $card .= ';' . $data['department'];
        }
        $card .= PHP_EOL;

        $card .= 'ADR;TYPE=work:';
        if (array_key_exists('work_po_box', $data)) {
            $card .= $data['work_po_box'] . ';';
        }
        if (array_key_exists('work_extended_address', $data)) {
            $card .= $data['work_extended_address'] . ';';
        }
        if (array_key_exists('work_address', $data)) {
            $card .= $data['work_address'] . ';';
        }
        if (array_key_exists('work_city', $data)) {
            $card .= $data['work_city'] . ';';
        }
        if (array_key_exists('work_state', $data)) {
            $card .= $data['work_state'] . ';';
        }
        if (array_key_exists('work_postal_code', $data)) {
            $card .= $data['work_postal_code'] . ';';
        }
        if (array_key_exists('work_country', $data)) {
            $card .= $data['work_country'] . PHP_EOL;
        }
        $card .= PHP_EOL;
        $card .= 'ADR;TYPE=home:';
        if (array_key_exists('home_po_box', $data)) {
            $card .= $data['home_po_box'] . ';';
        }
        if (array_key_exists('home_extended_address', $data)) {
            $card .= $data['home_extended_address'] . ';';
        }
        if (array_key_exists('home_address', $data)) {
            $card .= $data['home_address'] . ';';
        }
        if (array_key_exists('home_city', $data)) {
            $card .= $data['home_city'] . ';';
        }
        if (array_key_exists('home_state', $data)) {
            $card .= $data['home_state'] . ';';
        }
        if (array_key_exists('home_postal_code', $data)) {
            $card .= $data['home_postal_code'] . ';';
        }
        if (array_key_exists('home_country', $data)) {
            $card .= $data['home_country'] . PHP_EOL;
        }
        $card .= PHP_EOL;
        if (array_key_exists('email1', $data)) {
            $card .= 'EMAIL;TYPE=internet,pref:' . $data['email1'] . PHP_EOL;
        }
        if (array_key_exists('email2', $data)) {
            $card .= 'EMAIL;TYPE=internet:' . $data['email2'] . PHP_EOL;
        }
        if (array_key_exists('office_tel', $data)) {
            $card .= 'TEL;TYPE=work,voice:' . $data['office_tel'] . PHP_EOL;
        }
        if (array_key_exists('home_tel', $data)) {
            $card .= 'TEL;TYPE=home,voice:' . $data['home_tel'] . PHP_EOL;
        }
        if (array_key_exists('cell_tel', $data)) {
            $card .= 'TEL;TYPE=cell,voice:' . $data['cell_tel'] . PHP_EOL;
        }
        if (array_key_exists('fax_tel', $data)) {
            $card .= 'TEL;TYPE=work,fax:' . $data['fax_tel'] . PHP_EOL;
        }
        if (array_key_exists('pager_tel', $data)) {
            $card .= 'TEL;TYPE=work,pager:' . $data['pager_tel'] . PHP_EOL;
        }
        if (array_key_exists('url', $data)) {
            $card .= 'URL;TYPE=work:' . $data['url'] . PHP_EOL;
        }
        if (array_key_exists('birthday', $data)) {
            $card .= 'BDAY:' . $data['birthday'] . PHP_EOL;
        }
        if (array_key_exists('role', $data)) {
            $card .= 'ROLE:' . $data['role'] . PHP_EOL;
        }
        if (array_key_exists('note', $data)) {
            $card .= 'NOTE:' . $data['note'] . PHP_EOL;
        }

        if (array_key_exists('photo', $data)) {
            $image = $data['photo'];

            $imagetypes = array
            (
                IMAGETYPE_JPEG => 'JPEG',
                IMAGETYPE_GIF => 'GIF',
                IMAGETYPE_PNG => 'PNG',
                IMAGETYPE_BMP => 'BMP'
            );

            if ($imageinfo = @getimagesize($image) AND isset($imagetypes[$imageinfo[2]])) {
                $photo = base64_encode(file_get_contents($image));
                $type = $imagetypes[$imageinfo[2]];

                $path = 'PHOTO;ENCODING=BASE64;TYPE=' . $type . ':' . $photo;
            }

            $photo = base64_encode(file_get_contents($data['photo']));
            $card .= $path . PHP_EOL;
        }
        $card .= 'TZ:' . $data['timezone'] . PHP_EOL;
        $card .= 'END:VCARD' . PHP_EOL;
        return $card;
    }

    /** Create the card
     * @access public
     * @return void
     */
    public function createCard()
    {
        $data = $this->getData();
        $card = $this->buildCard($data);
        $filename = str_replace(' ', '_', $data['display_name']);
        header('Content-type: text/directory');
        header('Content-Disposition: attachment; filename=' . $filename . '.vcf');
        header('Pragma: public');
        return $card;
    }
}