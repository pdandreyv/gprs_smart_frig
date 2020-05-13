<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 */
class Settings
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $user_id;

    /**
     * @var integer
     */
    private $period_send_sms;

    /**
     * @var boolean
     */
    private $report_change_location;

    /**
     * @var string
     */
    private $report_phone;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user_id
     *
     * @param integer $userId
     * @return Settings
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    
        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set period_send_sms
     *
     * @param integer $periodSendSms
     * @return Settings
     */
    public function setPeriodSendSms($periodSendSms)
    {
        $this->period_send_sms = $periodSendSms;
    
        return $this;
    }

    /**
     * Get period_send_sms
     *
     * @return integer 
     */
    public function getPeriodSendSms()
    {
        return $this->period_send_sms;
    }

    /**
     * Set report_change_location
     *
     * @param boolean $reportChangeLocation
     * @return Settings
     */
    public function setReportChangeLocation($reportChangeLocation)
    {
        $this->report_change_location = $reportChangeLocation;
    
        return $this;
    }

    /**
     * Get report_change_location
     *
     * @return boolean 
     */
    public function getReportChangeLocation()
    {
        return $this->report_change_location;
    }

    /**
     * Set report_phone
     *
     * @param string $reportPhone
     * @return Settings
     */
    public function setReportPhone($reportPhone)
    {
        $this->report_phone = $reportPhone;
    
        return $this;
    }

    /**
     * Get report_phone
     *
     * @return string 
     */
    public function getReportPhone()
    {
        return $this->report_phone;
    }
    /**
     * @var integer
     */
    private $time_report;


    /**
     * Set time_report
     *
     * @param integer $timeReport
     * @return Settings
     */
    public function setTimeReport($timeReport)
    {
        $this->time_report = $timeReport;
    
        return $this;
    }

    /**
     * Get time_report
     *
     * @return integer 
     */
    public function getTimeReport()
    {
        return $this->time_report;
    }

    /**
     * @var integer
     */
    private $shelf_limit;

    /**
     * @var integer
     */
    private $icebox_limit;

    /**
     * @var integer
     */
    private $max_out_temperature;

    /**
     * @var integer
     */
    private $max_in_temperature;

    /**
     * Set shelf_limit
     *
     * @param integer $shelfLimit
     * @return Settings
     */
    public function setShelfLimit($shelfLimit)
    {
        $this->shelf_limit = $shelfLimit;
    
        return $this;
    }

    /**
     * Get shelf_limit
     *
     * @return integer 
     */
    public function getShelfLimit()
    {
        return $this->shelf_limit;
    }

    /**
     * Set icebox_limit
     *
     * @param integer $iceboxLimit
     * @return Settings
     */
    public function setIceboxLimit($iceboxLimit)
    {
        $this->icebox_limit = $iceboxLimit;
    
        return $this;
    }

    /**
     * Get icebox_limit
     *
     * @return integer 
     */
    public function getIceboxLimit()
    {
        return $this->icebox_limit;
    }

    /**
     * Set max_out_temperature
     *
     * @param integer $maxOutTemperature
     * @return Settings
     */
    public function setMaxOutTemperature($maxOutTemperature)
    {
        $this->max_out_temperature = $maxOutTemperature;
    
        return $this;
    }

    /**
     * Get max_out_temperature
     *
     * @return integer 
     */
    public function getMaxOutTemperature()
    {
        return $this->max_out_temperature;
    }

    /**
     * Set max_in_temperature
     *
     * @param integer $maxInTemperature
     * @return Settings
     */
    public function setMaxInTemperature($maxInTemperature)
    {
        $this->max_in_temperature = $maxInTemperature;
    
        return $this;
    }

    /**
     * Get max_in_temperature
     *
     * @return integer 
     */
    public function getMaxInTemperature()
    {
        return $this->max_in_temperature;
    }
    /**
     * @var integer
     */
    private $check_icebox;


    /**
     * Set check_icebox
     *
     * @param integer $checkIcebox
     * @return Settings
     */
    public function setCheckIcebox($checkIcebox)
    {
        $this->check_icebox = $checkIcebox;
    
        return $this;
    }

    /**
     * Get check_icebox
     *
     * @return integer 
     */
    public function getCheckIcebox()
    {
        return $this->check_icebox;
    }
    /**
     * @var string
     */
    private $emails;


    /**
     * Set emails
     *
     * @param string $emails
     * @return Settings
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
    
        return $this;
    }

    /**
     * Get emails
     *
     * @return string 
     */
    public function getEmails()
    {
        return $this->emails;
    }
}