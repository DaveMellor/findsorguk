<?php
/** Form for setting up types of staff role
 * 
 * An example of code:
 * <code>
 * <?php
 * $form = new StaffRoleForm();
 * $this->view->form = $form;
 * ?>
 * </code>
 * @author Daniel Pett <dpett at britishmuseum.org>
 * @category   Pas
 * @package    Pas_Form
 * @copyright  Copyright (c) 2011 DEJ Pett dpett @ britishmuseum . org
 * @license http://www.gnu.org/licenses/agpl-3.0.txt GNU Affero GPL v3.0
 * @version 1
 * @example /app/modules/admin/controllers/RolesController.php
*/
class StaffRoleForm extends Pas_Form {
    
    /** The constructor
     * @access public
     * @param array $options
     * @return void
     */
    public function __construct(array $options = null) {

	parent::__construct($options);
       
	$this->setName('staffroles');
			
	$role = new Zend_Form_Element_Text('role');
	$role->setLabel('Staff role title: ')
		->setRequired(true)
		->setAttrib('size',60)
		->addFilters(array('StripTags', 'StringTrim'))
		->addErrorMessage('Choose title for the role.')
		->addValidator('Alnum',true, array('allowWhiteSpace' => true));
	
	$description = new Pas_Form_Element_CKEditor('description');
	$description->setLabel('Role description: ')
                ->setRequired(true)
                ->setAttribs(array('rows' => 10, 'cols' => 80))
                ->addFilters(array('BasicHtml', 'WordChars', 'EmptyParagraph', 'StringTrim'));
	
	$valid = new Zend_Form_Element_Checkbox('valid');
	$valid->setLabel('Is this term valid?: ');
	
	//Submit button 
	$submit = new Zend_Form_Element_Submit('submit');

	$hash = new Zend_Form_Element_Hash('csrf');
	$hash->setValue($this->_salt)
		->setTimeout(4800);
	
	$this->addElements(array( $role, $description, $valid, $hash, $submit));
	
	$this->addDisplayGroup(array('role', 'description', 'valid', 'submit'), 'details');

	$this->details->setLegend('Activity details: ');
	
        parent::init();  
    }
}
