<?php

/* ******************************************************************************
 * History: 
 * $Log$
 * ****************************************************************************** 
 * Date : $Date$ 
 * Revision : $Revision$ 
 * ******************************************************************************
 */

/**
 * Contains all information for a Directory List request.
 */

require_once("IssuerEntry.php");

class DirectoryResponse 
{
    var $acquirerID = "";
    var $directoryDateTime = "";
    var $issuerShortList = array();
    var $issuerLongList = array();
	var $errorMessage = false;

    /**
     * @return Returns a list if IssuerEntry objects for the short listing only.
     * The List contains all Issuers that were sent by the acquirer System during the Directory Request.
     * The Issuers are stored as IssuerEntry objects.
     */
    function getIssuerShortList() 
    {
        return $this->issuerShortList;
    }

    function getIssuerLongList() 
    {
        return $this->issuerLongList;
    }

    function getIssuerFullList() 
    {
		sort($this->issuerShortList);
		sort($this->issuerLongList);
				
        $fullList = array_merge( $this->issuerShortList, $this->issuerLongList );

        return $fullList;        
    }

	/**
     * @return Returns the acquirerID from the answer XML message.
     */
    function getAcquirerID() 
    {
        return $this->acquirerID;
    }
    
    /**
     * @param sets the acquirerID 
     */
    function setAcquirerID($acquirerID) 
    {
        $this->acquirerID = $acquirerID;
    }

	/**
     * @return Returns the directory date/time stamp from the response XML message.
     */
    function getDirectoryDateTimeStamp() 
    {
        return $this->directoryDateTime;
    }
    
    /**
     * @param sets the directory date time stamp 
     */
    function setDirectoryDateTimeStamp($directoryDateTime) 
    {
        $this->directoryDateTime = $directoryDateTime;
    }
    /**
     * adds an Issuer to the IssuerList
     */
    function addIssuer( $entry ) 
    {
        if ( is_a( $entry, "IssuerEntry" ) ) 
        {
            if ( strcasecmp( $entry->getIssuerListType(), "short" ) == 0 ) 
            {
            	$this->issuerShortList[ $entry->getIssuerName() ] = $entry;
                ksort( $this->issuerShortList );
            } 
            else 
            {
            	$this->issuerLongList[ $entry->getIssuerName() ] = $entry;                
                ksort( $this->issuerLongList );
            }
        }
    }

	function IsResponseError()
	{
		return false;
	}
}

?>
