<?php 

namespace kirubha7\CloudwatchLogsLaravel; 
use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Exception;

class Cloudwatchlogs{

   /***Description */
   const DESCRIPTION = 'Cloudwatch Logs Laravel'; 

   /**Version */
   const VERSION = 'v1.0-beta'; 

   /**AWS clinet SDK params*/
   private $client;

   /**Success Status */
   const SUCCESS = true;

   /**Failure Status */
   const FAIL = false;

   /**Construct function*/
   function __construct(CloudWatchLogsClient $client)
   {
      $this->client = $client;
   }

   public static function customLog($log_msg)
   {
      $log_filename = "log";
      if (!file_exists($log_filename)) 
      {
         // create directory/folder uploads.
         mkdir($log_filename, 0777, self::SUCCESS);
      }
      $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.log';
      // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
      file_put_contents($log_file_data,print_r($log_msg,self::SUCCESS) . "\n", FILE_APPEND);
   } 

   public static function ping() : bool
   {
      return self::SUCCESS;
   }

   public  function putLog(string $groupName,string $streamName,string $retention  = '14',array $context = [],array $tags = []) : array
   {
      try{

         //Check and create log group
         $this->createLogGroup($groupName);

         //Check and create log stream
         $this->createLogStream($groupName,$streamName);

         $nextToken = null;

         //Get Log Stream Details
         $sequence_token = $this->getDescribeLogStream($groupName,$streamName);
         if(count($sequence_token) > 0 && 
               isset($sequence_token['logStreams'][0]['uploadSequenceToken']) &&
               !empty($sequence_token['logStreams'][0]['uploadSequenceToken'])){
                  $nextToken = $sequence_token['logStreams'][0]['uploadSequenceToken'];
         }

         //Send Logs to Cloudwatch
         $timestamp = floor(microtime(true) * 1000);
         $context = json_encode($context);
         $datas = [
            'logEvents' => [ // REQUIRED
                [
                    'message' => $context, // REQUIRED
                    'timestamp' => $timestamp, // REQUIRED
                ],
            ],
            'logGroupName' => $groupName, // REQUIRED
            'logStreamName' => $streamName, // REQUIRED
            'sequenceToken' => $nextToken
         ];

        /**If sequence token is not present no need to pass */
         if($datas['sequenceToken'] == null){
            unset($datas['sequenceToken']);
         }
         
         $this->client->putLogEvents($datas);

         return ['status' => self::SUCCESS ,'message' => 'Log sended successfully'];
      }catch(\Aws\Exception\AwsException $e){
         return ['status' => self::FAIL ,'error' => $e->getAwsErrorMessage()];
      }catch(\Exception $e){
         return ['status' => self::FAIL ,'error' => $e->getMessage()];
      }
   }

   /**
    * URL: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-logs-2014-03-28.html#createloggroup 
    * Description: Craete Log Group by given name by calling create log group api call*/
   public function createLogGroup(string $groupName,array $tags = []) :array
   {
      try{
         $check = $this->checkLogGroupExists($groupName);
         /**Check if log group already exists or not. 
          * Incase if log group is not present we need to create log group 
          * Incase if log group is present no need to call log group api call.
          * */
         if(isset($check['status']) && 
            $check['status'] == self::FAIL && 
            $check['error'] == 'Log Group Not Exists'){

               $this->client->createLogGroup([
                  'logGroupName' => $groupName, // REQUIRED
               ]);
         }

         return ['status' => self::SUCCESS ,'message' => 'Log Group Created Successfully'];
      }catch(\Aws\Exception\AwsException $e){
         return ['status' => self::FAIL ,'error' => $e->getAwsErrorMessage()];
      }catch(\Exception $e){
         return ['status' => self::FAIL ,'error' => $e->getMessage()];
      }
   }

   /**
    * URL: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-logs-2014-03-28.html#describeloggroups 
    * Desciption : Check log group name by given name by calling describe log group api call*/
   public function checkLogGroupExists(string $groupName) : array
   {
      try{
            $result = $this->client->describeLogGroups([
                                                   '  logGroupNamePrefix' => $groupName
                                                   ])
                                 ->get('logGroups');
            //Check if any log groups is present or not
            if(count($result) > 0){
               $groupNames = array_column($result,'logGroupName');
               if(in_array($groupName,$groupNames)){
                  return ['status' => self::SUCCESS ,'message' => 'Log Group Exists'];
               }else{
                  return ['status' => self::FAIL ,'error' => 'Log Group Not Exists'];
               }
            }else{
               return ['status' => self::FAIL ,'error' => 'Log Group Not Exists'];
            }
      }catch(\AWS\Exception\AwsException $e){
         return ['status' => self::FAIL ,'error' => $e->getAwsErrorMessage()];
      }catch(\Exception $e){
         return ['status' => self::FAIL ,'error' => $e->getMessage()];
      }
   }

   public function checkLogStreamExists($groupName,$streamName) : array
   {
      try{
            $result = $this->getDescribeLogStream($groupName,$streamName);
          
            //Check if any log stream is present or not
            if(isset($result['logStreams']) && count($result['logStreams']) > 0){
               $streamNames = array_column($result['logStreams'],'logStreamName');
               if(in_array($streamName,$streamNames)){
                  return ['status' => self::SUCCESS ,'message' => 'Log Stream Exists'];
               }else{
                  return ['status' => self::FAIL ,'error' => 'Log Stream Not Exists'];
               }
            }else{
               return ['status' => self::FAIL ,'error' => 'Log Stream Not Exists'];
            }

      }catch(\AWS\Exception\AwsException $e){
         return ['status' => self::FAIL ,'error' => $e->getAwsErrorMessage()];
      }catch(\Exception $e){
         return ['status' => self::FAIL ,'error' => $e->getMessage()];
      }
   }

   /**
    * URL: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-logs-2014-03-28.html#describelogstreams 
    * Description: Check log stream is present in given group by calling describe log stream api call */
   public function getDescribeLogStream($groupName,$streamName)
   {
      try{
         $response = [];
         $result = $this->client->describeLogStreams([
                                                   'logGroupName' => $groupName,
                                                   'logStreamNamePrefix' => $streamName
                                                   ]);
         $response = $result->toArray();
         return $response;
      }catch(\Aws\Exception\AwsException $e){
         return ['status' => self::FAIL ,'error' => $e->getAwsErrorMessage()];
      }catch(\Exception $e){
         return ['status' => self::FAIL ,'error' => $e->getMessage()];
      }
   }

   /**
    * URL: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-logs-2014-03-28.html#createlogstream 
    * Description: Create Log Stream by given name by calling create log stream apiaa*/
   public function createLogStream($groupName,$streamName) :array
   {
      try{
         $check = $this->checkLogStreamExists($groupName,$streamName);

         //Call CreateLogStream API
         if(isset($check['status']) &&
            $check['status'] == self::FAIL &&
            $check['error'] == 'Log Stream Not Exists'){
               $this->client->createLogStream([
                  'logGroupName' => $groupName,
                  'logStreamName' => $streamName,
               ]);
         }
         return ['status' => self::SUCCESS ,'message' => 'Log Stream Created Successfully'];
      }catch(\Aws\Exception\AwsException $e){
         return ['status' => self::FAIL ,'error' => $e->getAwsErrorMessage()];
      }catch(\Exception $e){
         return ['status' => self::FAIL ,'error' => $e->getMessage()];
      }
   }

}