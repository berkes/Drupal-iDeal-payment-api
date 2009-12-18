<?php
/* ******************************************************************************
 * History: 
 * $Log$
 * 
 * ****************************************************************************** 
 * Date : $Date$ 
 * Revision : $Revision$ 
 * ******************************************************************************
 */
 
/**
 * This class contains all necessary data that can be returned from a iDEAL AcquirerTrxRequest.
 */

class AcquirerStatusResponse 
{
	var $acquirerID;
    var $consumerName = "";
    var $consumerAccountNumber = "";
    var $consumerCity = "";
    var $transactionID = "";
    var $status = "";
	var $errorMessage = false;
    
    /**
     * @return Returns the acquirerID.
     */
    function getAcquirerID() 
    {
        return $this->acquirerID;
    }
    /**
     * @param acquirerID The acquirerID to set. (mandatory)
     */
    function setAcquirerID( $acquirerID ) 
    {
        $this->acquirerID = $acquirerID;
    }
    /**
     * @return Returns the consumerAccountNumber.
     */
    function getConsumerAccountNumber() 
    {
        return $this->consumerAccountNumber;
    }
    
    /**
     * @param consumerAccountNumber The consumerAccountNumber to set.
     */
    function setConsumerAccountNumber( $consumerAccountNumber ) 
    {
        $this->consumerAccountNumber = $consumerAccountNumber;
    }
    
    /**
     * @return Returns the consumerCity.
     */
    function getConsumerCity() 
    {
        return $this->consumerCity;
    }
    
    /**
     * @param consumerCity The consumerCity to set.
     */
    function setConsumerCity( $consumerCity ) 
    {
        $this->consumerCity = $consumerCity;
    }
    
    /**
     * @return Returns the consumerName.
     */
    function getConsumerName() 
    {
        return $this->consumerName;
    }
    
    /**
     * @param consumerName The consumerName to set.
     */
    function setConsumerName( $consumerName ) 
    {
        $this->consumerName = $consumerName;
    }
    
    /**
     * @return Returns the transactionID.
     */
    function getTransactionID() 
    {
        return $this->transactionID;
    }
    
    /**
     * @param transactionID The transactionID to set.
     */
    function setTransactionID( $transactionID ) 
    {
        $this->transactionID = $transactionID;
    }
    
    /**
     * @return Returns the status. See the definitions
     */
    function getStatus() 
    {
        return $this->status;
    }
    
    /**
     * @param status The status to set. See the definitions
     */
    function setStatus( $status ) 
    {
        $this->status = $status;
    }
	
	function IsResponseError()
	{
		return false;
	}

}

?>
