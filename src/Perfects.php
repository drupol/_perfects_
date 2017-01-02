<?php

namespace drupol\perfectsbot;

use Abraham\TwitterOAuth\TwitterOAuth;
use drupol\phpermutations\Iterators\Perfect;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Perfects.
 *
 * @package drupol\Perfectsbot
 */
class Perfects {

  /**
   * @var LoggerInterface
   */
  protected $logger;

  /**
   * @var array
   */
  protected $config;

  /**
   * Perfects constructor.
   */
  public function __construct() {
    $logger = new Logger('PerfectsBot');
    $logger->pushHandler(new \Monolog\Handler\NullHandler());
    $this->setLogger($logger);
    $this->config = Yaml::parse(file_get_contents('config.yml'));
  }

  /**
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * @return \Psr\Log\LoggerInterface
   */
  public function getLogger() {
    return $this->logger;
  }

  /**
   * Start the bot
   */
  public function start() {
    $this->getLogger()->info(sprintf('Started...'));

    $already_found = $this->getAlreadyFoundPerfectsNumbers();

    $perfects = new Perfect();
    $perfects->setMinLimit(reset($already_found));

    foreach ($perfects as $number) {
      $this->getLogger()->info(sprintf('Found a new Perfect number: %s', $number));

      if (!in_array($number, $this->getAlreadyFoundPerfectsNumbers())) {
        $this->getLogger()->info(sprintf('Sending a notification on Twitter...'));
        $this->postToTwitter($number);
      } else {
        $this->getLogger()->info(sprintf('Not sending a notification on Twitter...'));
      }
    }
  }

  /**
   * Return the list of perfects numbers already found.
   *
   * @return int[]
   */
  public function getAlreadyFoundPerfectsNumbers() {
    $twitter = $this->config['twitter'];
    $connection = new TwitterOAuth($twitter['consumer_key'], $twitter['consumer_secret'], $twitter['access_token'], $twitter['access_token_secret']);
    $statuses = $connection->get("statuses/home_timeline", ["count" => 100, "exclude_replies" => true]);

    $already_found = array();
    foreach ($statuses as $status) {
      if (isset($status->text)) {
        if (is_numeric($status->text)) {
          $already_found[$status->text] = $status->text;
        }
      }
    }

    return $already_found;
  }

  /**
   * Post a message on Twitter.
   *
   * @param $message
   */
  public function postToTwitter($message) {
    $twitter = $this->config['twitter'];
    $connection = new TwitterOAuth($twitter['consumer_key'], $twitter['consumer_secret'], $twitter['access_token'], $twitter['access_token_secret']);
    $connection->post("statuses/update", ["status" => $message]);
  }

}