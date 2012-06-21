<?php

class OrderStatusLog_CheckAvailability extends OrderStatusLog {

	public static $defaults = array(
		"InternalUseOnly" => true
	);

	public static $db = array(
		'AvailabilityChecked' => "Boolean",
		'AvailabilityChanged' => "Boolean",
		'AdminComment' => "Text"
	);

	/**
	*
	*@return Boolean
	**/
	public function canDelete($member = null) {
		return false;
	}

	public static $searchable_fields = array(
		'OrderID' => array(
			'field' => 'NumericField',
			'title' => 'Order Number'
		),
		"AvailabilityChecked" => true
	);

	public static $summary_fields = array(
		"Created" => "Date",
		"Author.Title" => "Checked by",
		"AvailabilityCheckedNice" => "Availability Checked"
	);

	public static $casting = array(
		"AvailabilityCheckedNice" => "Varchar"
	);

	function AvailabilityCheckedNice() {return $this->getAvailabilityCheckedNice();}
	function getAvailabilityCheckedNice() {if($this->AvailabilityChecked) {return _t("OrderStatusLog.YES", "yes");}return _t("OrderStatusLog.No", "no");}

	public static $singular_name = "Availability Check";
		function i18n_singular_name() { return _t("OrderStatusLog.AVAILABILITYCHECK", "Availability Check");}

	public static $plural_name = "Availability Checks";
		function i18n_plural_name() { return _t("OrderStatusLog.AVAILABILITYCHECKS", "Availability Checks");}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName("Title");
		$fields->removeByName("Note");
		$fields->removeByName("InternalUseOnly");
		$fields->removeByName("EmailSent");
		$fields->addFieldToTab(
			'Root.Main',
			new CheckboxField("AvailabilityChecked", _t("OrderStatusLog.CHECKED", "Availability is confirmed (we can proceed with this order)"))
		);
		return $fields;
	}

	function scaffoldSearchFields(){
		$fields = parent::scaffoldSearchFields();
		$fields->replaceField("OrderID", new NumericField("OrderID", "Order Number"));
		return $fields;
	}

	function onBeforeWrite() {
		parent::onBeforeWrite();
	}


	function onAfterWrite(){
		if($this->AvailabilityChecked) {
			if($order = $this->Order()) {
				$order->tryToFinaliseOrder();
			}
		}
	}

	/**
	*
	*@return String
	**/
	function getCustomerNote() {
		if($this->Author()) {
			if($this->AvailabilityChanged) {
				return _t("OrderStatus.AVAILABILITYCONFIRMEDBY", "Availability Confirmed by: ").$this->Author()->getTitle()." | ".$this->AdminComment;
			}
			else {
				return _t("OrderStatus.AVAILABILITYCHANGEDBY", "Availability CHANGED by: ").$this->Author()->getTitle()." | ".$this->AdminComment;
			}
		}
	}

	function CustomerNote(){
		return $this->getCustomerNote();
	}




}


