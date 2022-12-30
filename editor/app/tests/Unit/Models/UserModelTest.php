<?

namespace EasypageTests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Easypage\Models\UserModel;

class UserModelTest extends TestCase
{
    private $model;

    protected function setUp(): void
    {
        $this->model = new UserModel;
    }

    public function testFirst(): void
    {
        $this->assertTrue(is_a($this->model, UserModel::class));
    }
}
