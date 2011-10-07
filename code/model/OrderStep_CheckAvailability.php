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
		return true;
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


	function addOrderStepFields(&$fields, $order) {
		$order->tryToFinaliseOrder();
		OrderStatusLog::add_available_log_classes_array("OrderStatusLog_CheckAvailability");
		$msg = _t("OrderStep.MUSTDOAVAILABILITYCHECK", " ... To move this order to the next step you must carry out a availability check (are the products available) by creating a record here (click me)");
		$fields->addFieldToTab("Root.Next", $order->OrderStatusLogsTable("OrderStatusLog_CheckAvailability", $msg));
		return $fields;
	}


}
