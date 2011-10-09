<?php


class OrderStep_CheckAvailability extends OrderStep {

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
		$replacementArray["Order"] = $order;
		$replacementArray["EmailLogo"] = SiteConfig::current_site_config()->EmailLogo();
 		$from = Order_Email::get_from_email();
 		//why are we using this email and NOT the member.EMAIL?
 		//for historical reasons????
 		$to = Order_Email::get_from_email();
 		if($from && $to) {
			$subject = _t("OrderStep_CheckAvailability.NEWORDERTOBECHECKED", "New order to be checked");
			//TO DO: should be a payment specific message as well???
			$email = new Order_ReceiptEmail();
			if(!($email instanceOf Email)) {
				user_error("No correct email class provided.", E_USER_ERROR);
			}
			$email->setFrom($from);
			$email->setTo($to);
			$email->setSubject();
			$email->populateTemplate($replacementArray);
			return $email->send(null, $order, false);
		}
	}

	/**
	 *@param DataObject $order Order
	 *@return DataObject | Null - DataObject = OrderStep
	 **/
	public function nextStep($order) {
		if(DataObject::get_one("OrderStatusLog_CheckAvailability", "\"OrderID\" = ".$order->ID." AND \"AvailabilityChecked\" = 1")) {
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
		OrderStatusLog::add_available_log_classes_array("OrderStatusLog_CheckAvailability");
		$msg = _t("OrderStep.MUSTDOAVAILABILITYCHECK", " ... To move this order to the next step you must carry out a availability check (are the products available) by creating a record here (click me)");
		$fields->addFieldToTab("Root.Next", $order->OrderStatusLogsTable("OrderStatusLog_CheckAvailability", $msg));
		return $fields;
	}


}
