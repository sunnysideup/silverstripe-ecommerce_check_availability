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

	protected static $true_and_false_definitions = array(
		"yes" => 1,
		"no" => 0
	);
		static function set_true_and_false_definitions(array $a) {self::$true_and_false_definitions = $a;}
		static function get_true_and_false_definitions() {return self::$true_and_false_definitions;}

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
			new CheckboxField("AvailabilityChecked", _t("OrderStatusLog.CHECKED", "Availability is confirmed"))
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

	function onAfterWrite() {
		parent::onAfterWrite();
		if($this->AvailabilityChecked) {
			$order = $this->Order();
			if($order) {
				if(!$order->IsSubmitted()) {
					$className = OrderStatusLog::get_order_status_log_class_used_for_submitting_order();
					if(class_exists($className)) {
						$obj = new $className();
						if($obj instanceOf OrderStatusLog) {
							$obj->OrderID = $order->ID;
							$obj->Title = $this->Name;
							$saved = false;
							if($this->SaveOrderAsJSON)                        {$obj->OrderAsJSON = $order->ConvertToJSON(); $saved = true;}
							if($this->SaveOrderAsHTML)                        {$obj->OrderAsHTML = $order->ConvertToHTML(); $saved = true;}
							if($this->SaveOrderAsSerializedObject|| !$saved)  {$obj->OrderAsString = $order->ConvertToString();$saved = true; }
							$obj->write();
						}
						else {
							user_error('OrderStatusLog::$order_status_log_class_used_for_submitting_order refers to a class that is NOT an instance of OrderStatusLog');
						}

					}
					else {
						user_error('OrderStatusLog::$order_status_log_class_used_for_submitting_order refers to a non-existing class');
					}
				}
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


