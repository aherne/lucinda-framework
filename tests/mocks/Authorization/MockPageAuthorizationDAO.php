<?php
namespace Test\Lucinda\Project\mocks\Authorization;

use Lucinda\WebSecurity\Authorization\DAO\PageAuthorizationDAO;

class MockPageAuthorizationDAO extends PageAuthorizationDAO
{
    protected function detectID(string $pageURL): ?int
    {
        switch ($pageURL) {
            case "login":
                return 1;
                break;
            case "index":
                return 2;
                break;
            case "administration":
                return 3;
                break;
            default:
                return null;
                break;
        }
    }
    
    public function isPublic(): bool
    {
        switch ($this->pageID) {
            case 1:
                return true;
                break;
            case 2:
                return false;
                break;
            case 3:
                return false;
                break;
        }
    }
}
