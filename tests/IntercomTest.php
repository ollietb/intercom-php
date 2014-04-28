<?php
class IntercomTest extends PHPUnit_Framework_TestCase
{
    protected $service;

    protected function setUp()
    {
        if ($GLOBALS['integrationTests']) {
            $this->service = new Intercom($GLOBALS['appId'],
                                          $GLOBALS['apiKey']);
        } else {
            $this->service = $this->getMockBuilder('Intercom')
                                  ->disableOriginalConstructor()
                                  ->getMock();
            $methods = array('getAllUsers',
                             'createUser',
                             'getUser',
                             'updateUser',
                             'createImpression',
                             'createEvent');
            foreach ($methods as $method) {
                $this->service->expects($this->any())
                              ->method($method)
                              ->will($this->returnValue(
                                     json_decode(
                                        file_get_contents('tests/mocks/' . $method . '.mock')
                                     )
                                ));
            }
            $this->service->expects($this->any())
                          ->method('getLastError')
                          ->will($this->returnValue(
                                 json_decode(
                                    file_get_contents('tests/mocks/getLastError.mock')
                                    , true
                                 )
                            ));
        }
    }

    public function testGetAllUsers()
    {
        $users = $this->service->getAllUsers(1, 1);

        // Retry if failing on the first attempt.
        if (!is_object($users)) {
            $users = $this->service->getAllUsers(1, 1);
        }

        $lastError = $this->service->getLastError();

        $this->assertTrue(is_object($users), $lastError['code'] . ': ' . $lastError['message']);
        $this->assertObjectHasAttribute('users', $users);
    }

    /**
     * @group Travis
     */
    public function testCreateUser()
    {
        $userId = 'userId001';
        $email = 'email@example.com';
        $res = $this->service->createUser('userId001', $email);
        $lastError = $this->service->getLastError();

        $this->assertTrue(is_object($res), $lastError['code'] . ': ' . $lastError['message']);
        $this->assertObjectHasAttribute('email', $res);
        $this->assertEquals($email, $res->email);
        $this->assertObjectHasAttribute('user_id', $res);
        $this->assertEquals($userId, $res->user_id);
    }

    /**
     * @depends testCreateUser
     */
    public function testGetUser()
    {
        $res = $this->service->getUser('userId001');
        $lastError = $this->service->getLastError();

        $this->assertTrue(is_object($res), $lastError['code'] . ': ' . $lastError['message']);
        $this->assertObjectHasAttribute('email', $res);
        $this->assertObjectHasAttribute('user_id', $res);
    }

    /**
     * @group Travis
     */
    public function testUpdateUser()
    {
        $userId = 'userId001';
        $email = 'new+email@example.com';
        $res = $this->service->updateUser('userId001', $email);
        $lastError = $this->service->getLastError();

        $this->assertTrue(is_object($res), $lastError['code'] . ': ' . $lastError['message']);
        $this->assertObjectHasAttribute('email', $res);
        $this->assertEquals($email, $res->email);
        $this->assertObjectHasAttribute('user_id', $res);
        $this->assertEquals($userId, $res->user_id);
    }

    /**
     * @group Travis
     */
    public function testCreateImpression()
    {
        $res = $this->service->createImpression('userId001');
        $lastError = $this->service->getLastError();

        $this->assertTrue(is_object($res), $lastError['code'] . ': ' . $lastError['message']);
        $this->assertObjectHasAttribute('unread_messages', $res);
    }
}
