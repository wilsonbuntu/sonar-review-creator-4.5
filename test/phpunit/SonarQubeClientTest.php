<?php

class SonarQubeClientTest extends PHPUnit_Framework_TestCase {

  private $sonarQubeClient;
  private $project = 'com.tomslabs.tools:sonar-review-creator';
  private $priorities = 'BLOCKER,CRITICAL,MAJOR';
  private $depth = '-1';
  
  private $projectViolationsJson;
  
  public function setUp() {
    $this->sonarQubeClient = new SonarQubeClient();
    $this->projectViolationsJson = $this->readJsonResultFromFile('_fixtures/projectViolationsJson.json');
  }
  
  private function readJsonResultFromFile($fileName) {
    $file = dirname(__FILE__).'/'.$fileName;
    return file_get_contents($file);  
  }    
  
  /** @test */
  public function getConfFromIniFile() {
    assertThat($this->sonarQubeClient->getSonarHost(), equalTo('sonar.mycompany.com'));
    assertThat($this->sonarQubeClient->getAssignerUsername(), equalTo('sonaradmin'));
    assertThat($this->sonarQubeClient->getAssignerPassword(), equalTo('password'));
  }
  
  /** @test */
  public function buildGetViolationsUrl() {
    assertThat($this->sonarQubeClient->buildGetViolationsUrl($this->project, $this->depth, $this->priorities), equalTo("http://sonar.mycompany.com/api/violations?resource=com.tomslabs.tools:sonar-review-creator&depth=-1&priorities=BLOCKER,CRITICAL,MAJOR&format=json"));
  }  
  
  /** @test */
  public function readFirstViolationFromStubResponse() {
    $sonarQubeClient = $this->mockSonarQubeClient();
    $violations = $sonarQubeClient->getViolations($this->project, $this->depth, $this->priorities);
    
    $firstViolation = $violations[0];
    
    $violationLineNumber = $firstViolation->line;
    $violationResource = $firstViolation->resource;
    $violatedFile = $violationResource->key;
    
    $violatedFullFilePath = array_pop(explode(':', $violatedFile));
    
    assertThat(count($violations), equalTo(5));
    assertThat($violationLineNumber, equalTo(249));
    assertThat($violatedFile, equalTo("com.tomslabs.tools:sonar-review-creator:lib/helper/TomsLabsPager.class.php"));
    assertThat($violatedFullFilePath, equalTo("lib/helper/TomsLabsPager.class.php"));
  }  
  
  private function mockSonarQubeClient() {
    $sonarQubeClient = $this->getMock('SonarQubeClient', array('getViolations'), array($this->project, $this->depth, $this->priorities), '', true);
    $sonarQubeClient->expects($this->once())
                    ->method('getViolations')
                    ->will($this->returnValue(json_decode($this->projectViolationsJson)));    
    return $sonarQubeClient;
  }
  
}