<?php


class OrderStep_CheckAvailability extends OrderStep {

	public static $db = array(
		"MinimumOrderAmount" => "Int"
	);

	public static $defaults = array(
		"CustomerCanEdit" => 1,
		"CustomerCanCancel" => 1,
		"CustomerCanPay" => 1,
		"Name" => "Check Availability",
		"Code" => "CHECKAVAILABILITY",
		"Sort" => 15,
		"ShowAsInProcessOrder" => 1
	);

	public function initStep($order) {
		return true;
	}

	public function doStep($order) {
		$noNeedToCheck = false;
		if($this->doesNotNeedToBeChecked($order) ) {
			$subject = $this->EmailSubject;
			if($subject) {
				$message = $this->CustomerMessage;
				if(!$this->hasBeenSent($order)) {
					$order->sendEmail($subject, $message, $resend = false, $adminOnly = false, $emailClass = 'Order_StatusEmail');
				}
			}
		}
		else {
			$noNeedToCheck =  true;
		}
		if($noNeedToCheck || $this->hasBeenChecked($order) ) {
			if(!$order->IsSubmitted()) {
				$className = EcommerceConfig::get("OrderStatusLog", "order_status_log_class_used_for_submitting_order");
				if(class_exists($className)) {
					$obj = new $className();
					if($obj instanceOf OrderStatusLog) {
						$obj->OrderID = $order->ID;
						$obj->Title = $this->Name;
						//it is important we add this here so that we can save the 'submitted' version.
						//this is particular important for the Order Item Links.
						$obj->write();
						$saved = false;
						if($this->SaveOrderAsJSON)												{$obj->OrderAsJSON = $order->ConvertToJSON(); $saved = true;}
						if($this->SaveOrderAsHTML)												{$obj->OrderAsHTML = $order->ConvertToHTML(); $saved = true;}
						if($this->SaveOrderAsSerializedObject || !$saved)	{$obj->OrderAsString = $order->ConvertToString();$saved = true; }
						$obj->write();
					}
					else {
						user_error('EcommerceConfig::get("OrderStatusLog", "order_status_log_class_used_for_submitting_order") refers to a class that is NOT an instance of OrderStatusLog');
					}
				}
				else {
					user_error('EcommerceConfig::get("OrderStatusLog", "order_status_log_class_used_for_submitting_order") refers to a non-existing class');
				}
			}
			return true;
		}
		return false;
	}

	/**
	 *@param DataObject $order Order
	 *@return DataObject | Null - DataObject = OrderStep
	 **/
	public function nextStep($order) {
		if( ($this->doesNotNeedToBeChecked($order)) || $this->hasBeenChecked($order) ) {
			return parent::nextStep($order);
		}
		return null;
	}

	/**
	 * Allows the opportunity for the Order Step to add any fields to Order::getCMSFields
	 *@param FieldSet $fields
	 *@param Order $order
	 *@return FieldSet
	 **/
	function addOrderStepFields(&$fields, $order) {
		$msg = _t("OrderStep.MUSTDOAVAILABILITYCHECK", " ... To move this order to the next step you must carry out a availability check (are the products available) by creating a record here (click me)");
		$fields->addFieldToTab("Root.Next", $order->OrderStatusLogsTable("OrderStatusLog_CheckAvailability", $msg));
		return $fields;
	}


	/**
	 * tells the order to display itself with an alternative display page.
	 * in that way, orders can be displayed differently for certain steps
	 * for example, in a print step, the order can be displayed in a
	 * PRINT ONLY format.
	 *
	 * When the method return null, the order is displayed using the standard display page
	 * @see Order::DisplayPage
	 *
	 *
	 * @return Null|Object (Page)
	 **/
	public function AlternativeDisplayPage() {
		return DataObject::get_one("OrderConfirmationPage");
	}

	/**
	 * Explains the current order step.
	 * @return String
	 */
	protected function myDescription(){
		return _t("OrderStep_CheckAvailability.DESCRIPTION", "Allows the shop admin to check product availability for confirming order.");
	}


	protected function doesNotNeedToBeChecked($order){
		return DataObject::get_one("OrderStatusLog_CheckAvailability", "\"OrderID\" = ".$order->ID." AND \"AvailabilityChecked\" = 1");
	}

	protected function hasBeenChecked($order){
		return DataObject::get_one("OrderStatusLog_CheckAvailability", "\"OrderID\" = ".$order->ID." AND \"AvailabilityChecked\" = 1");
	}
}
