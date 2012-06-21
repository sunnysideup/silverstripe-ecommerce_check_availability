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
		if($order->Total() > $this->MinimumOrderAmount) {
			$subject = $this->EmailSubject;
			$message = $this->CustomerMessage;
			if(!$this->hasBeenSent($order)) {
				$order->sendStatusChange($subject, $message);
			}
		}
		return true;
	}

	/**
	 *@param DataObject $order Order
	 *@return DataObject | Null - DataObject = OrderStep
	 **/
	public function nextStep($order) {
		if($order->Total() < $this->MinimumOrderAmount) {
			return parent::nextStep($order);
		}
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

}
