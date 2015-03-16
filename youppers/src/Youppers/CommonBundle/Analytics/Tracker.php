<?php

namespace Youppers\CommonBundle\Analytics;

use Happyr\Google\AnalyticsBundle\Service\Tracker as GoogleTracker;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class Tracker
{

	function __construct(GoogleTracker $tracker, LoggerInterface $logger) {
		$this->tracker = $tracker;
		$this->logger = $logger;
	}
	
	/**
	 * Send data to Google Analytics
	 * @param array $data
	 */
	public function send($data)
	{
		if (!array_key_exists('geoid',$data)) {
			$data['geoid'] = 2380; // Italy	IT Country
		}
		
    	$stopwatch = new Stopwatch();
    	$stopwatch->start('GoogleAnalytics');
    	$res = $this->tracker->send($data);
    	$event = $stopwatch->stop('GoogleAnalytics');
    	if ($res) {
    		$this->logger->info("Sent event to GoogleAnalytics: " . $event->getDuration() . "mS " . var_export($data, true));
    	} else {
    		$this->logger->error("Failed sending event to GoogleAnalytics: " . var_export($data, true));
    	}
    }

}
