<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Directory;
use App\Models\Group;
use App\Models\Report;
use App\Models\ReportDataView;
use App\Models\ReportParameterInput;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class APIUserOrg1_AD_Org2_D_Org3_VTest extends TestCase
{
    private function _login(int $expectedOrganizationId = 1, int $organizationId = 0): bool
    {
        $response = $this->postLogin('org1_ad-org2_d-org3_v', $organizationId)
            ->assertStatus(200);

        if ($response->status() === 200) {

            $response
                ->assertJson(
                    fn(AssertableJson $json) => $json
                        ->where('data.is_super_admin', false)
                        ->where('data.id', 2)
                        ->where('data.current_organization_user.organization_id', $expectedOrganizationId)
                        ->has('data.organization_users')
                        ->count('data.organization_users', 3)
                        ->etc()
                );

        }

        return $response->status() === 200;
    }

    public function testApiLogins()
    {
        $this->assertTrue($this->_login(1, 1));
        $this->postLogout()->assertStatus(200);

        $this->assertTrue($this->_login(2, 2));
        $this->postLogout()->assertStatus(200);

        $this->assertTrue($this->_login(3, 3));
        $this->postLogout()->assertStatus(200);

        $this->assertTrue($this->_login());
        $this->postLogout()->assertStatus(200);
    }

    //
    // Category
    //
    public function testApiCategoryFromOrganization1CategoriesListing()
    {
        $this->assertTrue($this->_login());

        $this->get($this->appUrl . '/api/category', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 3)
                    ->etc()
            );
    }

    public function testApiCategoryFromOrganization2CategoriesListing()
    {
        $this->assertTrue($this->_login(2, 2));

        // Category from Organization 1
        $this->get($this->appUrl . '/api/category', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 2)
                    ->etc()
            );
    }

    public function testApiCategoryFromOrganization3CategoriesListing()
    {
        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/category', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 3)
                    ->etc()
            );
    }

    public function testApiCategoryFromOrganization1CategoriesShow()
    {
        $this->assertTrue($this->_login());

        // Category from Organization 1
        $this->get($this->appUrl . '/api/category/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/category/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/category/3', $this->headers)
            ->assertStatus(200);

        // Category from Organization 2
        $this->get($this->appUrl . '/api/category/4', $this->headers)
            ->assertStatus(401);

        // Category from Organization 3
        $this->get($this->appUrl . '/api/category/6', $this->headers)
            ->assertStatus(401);
    }

    public function testApiCategoryFromOrganization2CategoriesShow()
    {
        $this->assertTrue($this->_login(2, 2));

        // Category from Organization 1
        $this->get($this->appUrl . '/api/category/1', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/category/2', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/category/3', $this->headers)
            ->assertStatus(401);

        // Category from Organization 2
        $this->get($this->appUrl . '/api/category/4', $this->headers)
            ->assertStatus(200);

        // Category from Organization 3
        $this->get($this->appUrl . '/api/category/6', $this->headers)
            ->assertStatus(401);
    }

    public function testApiCategoryFromOrganization3CategoriesShow()
    {
        $this->assertTrue($this->_login(3, 3));

        // Category from Organization 1
        $this->get($this->appUrl . '/api/category/1', $this->headers)
            ->assertStatus(401);
        $this->get($this->appUrl . '/api/category/2', $this->headers)
            ->assertStatus(401);

        // Category from Organization 2
        $this->get($this->appUrl . '/api/category/4', $this->headers)
            ->assertStatus(401);

        // Category from Organization 3
        $this->get($this->appUrl . '/api/category/6', $this->headers)
            ->assertStatus(200);
    }

    public function testApiCategoryFromOrganization1Crud()
    {
        $this->assertTrue($this->_login());

        // Category from Organization 1
        $response = $this->postCategory()
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Category')
                    ->where('data.organization_id', 1)
                    ->etc()
            );

        /** @var Category $category */
        $category = $response->original['data']->resource;

        $this->putCategory($category->id)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Category Updated')
                    ->where('data.organization_id', 1)
                    ->etc()
            );

        $this->deleteCategory($category->id)
            ->assertStatus(200);

        // Category from Organization 2
        $this->postCategory(2)
            ->assertStatus(401);

        $this->putCategory(4, 2)
            ->assertStatus(401);

        $this->deleteCategory(4)
            ->assertStatus(401);

        // Category from Organization 3
        $this->postCategory(3)
            ->assertStatus(401);

        $this->putCategory(6, 3)
            ->assertStatus(401);

        $this->deleteCategory(6)
            ->assertStatus(401);
    }

    public function testApiCategoryFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2));

        // Category from Organization 2
        $response = $this->postCategory(2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Category')
                    ->where('data.organization_id', 2)
                    ->etc()
            );

        /** @var Category $category */
        $category = $response->original['data']->resource;

        $this->putCategory($category->id, 2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Category Updated')
                    ->where('data.organization_id', 2)
                    ->etc()
            );

        $this->deleteCategory($category->id)
            ->assertStatus(200);

        // Category from Organization 1
        $this->postCategory()
            ->assertStatus(401);

        $this->putCategory(1)
            ->assertStatus(401);

        $this->deleteCategory(1)
            ->assertStatus(401);

        // Category from Organization 3
        $this->postCategory(3)
            ->assertStatus(401);

        $this->putCategory(6, 3)
            ->assertStatus(401);

        $this->deleteCategory(6)
            ->assertStatus(401);
    }

    public function testApiCategoryFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));

        // Category from Organization 3
        $this->postCategory(3)
            ->assertStatus(401);


        $this->putCategory(6, 3)
            ->assertStatus(401);

        $this->deleteCategory(6)
            ->assertStatus(401);

        // Category from Organization 2
        $this->postCategory(2)
            ->assertStatus(401);

        $this->putCategory(4, 2)
            ->assertStatus(401);

        $this->deleteCategory(4)
            ->assertStatus(401);

        // Category from Organization 1
        $this->postCategory()
            ->assertStatus(401);

        $this->putCategory(1)
            ->assertStatus(401);

        $this->deleteCategory(1)
            ->assertStatus(401);
    }

    //
    // ConfConnector
    //
    public function testApiConfConnectorFromOrganization1ConfConnectorListing()
    {
        $this->assertTrue($this->_login(1, 1));

        $this->get($this->appUrl . '/api/conf-connector', $this->headers)
            ->assertStatus(200);
    }

    public function testApiConfConnectorFromOrganization2ConfConnectorListing()
    {
        $this->assertTrue($this->_login(2, 2));

        $this->get($this->appUrl . '/api/conf-connector', $this->headers)
            ->assertStatus(200);
    }

    public function testApiConfConnectorFromOrganization3ConfConnectorListing()
    {
        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/conf-connector', $this->headers)
            ->assertStatus(401);
    }

    public function testApiConfConnectorFromOrganization1ConfConnectorShow()
    {
        $this->assertTrue($this->_login(1, 1));

        // ConfConnector from Organization 1
        $this->get($this->appUrl . '/api/conf-connector/1', $this->headers)
            ->assertStatus(200);

        // ConfConnector from Organization 2
        $this->get($this->appUrl . '/api/conf-connector/2', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/5', $this->headers)
            ->assertStatus(401);

        // ConfConnector from Organization 3
        $this->get($this->appUrl . '/api/conf-connector/3', $this->headers)
            ->assertStatus(401);
    }

    public function testApiConfConnectorFromOrganization2ConfConnectorShow()
    {
        $this->assertTrue($this->_login(2, 2));

        // ConfConnector from Organization 1
        $this->get($this->appUrl . '/api/conf-connector/1', $this->headers)
            ->assertStatus(401);

        // ConfConnector from Organization 2
        $this->get($this->appUrl . '/api/conf-connector/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/conf-connector/5', $this->headers)
            ->assertStatus(200);

        // ConfConnector from Organization 3
        $this->get($this->appUrl . '/api/conf-connector/3', $this->headers)
            ->assertStatus(401);
    }

    public function testApiConfConnectorFromOrganization3ConfConnectorShow()
    {
        $this->assertTrue($this->_login(3, 3));

        // ConfConnector from Organization 1
        $this->get($this->appUrl . '/api/conf-connector/1', $this->headers)
            ->assertStatus(401);

        // ConfConnector from Organization 2
        $this->get($this->appUrl . '/api/conf-connector/2', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/5', $this->headers)
            ->assertStatus(401);

        // ConfConnector from Organization 3
        $this->get($this->appUrl . '/api/conf-connector/3', $this->headers)
            ->assertStatus(401);
    }

    public function testApiConfConnectorFromOrganization2to3PrimeReactTreeDbConfConnector()
    {
        $this->assertTrue($this->_login(2, 2));

        $this->get($this->appUrl . '/api/conf-connector/2/prime-react-tree-db', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/conf-connector/3/prime-react-tree-db', $this->headers)
            ->assertStatus(401);

        $this->assertTrue($this->getChangeOrganizationUser(3));

        $this->get($this->appUrl . '/api/conf-connector/2/prime-react-tree-db', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/3/prime-react-tree-db', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/5/prime-react-tree-db', $this->headers)
            ->assertStatus(401);
    }

    public function testApiConfConnectorFromOrganization1PrimeReactTreeDbConfConnector()
    {
        $this->assertTrue($this->_login(1, 1));

        $this->get($this->appUrl . '/api/conf-connector/1/prime-react-tree-db', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/conf-connector/2/prime-react-tree-db', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/3/prime-react-tree-db', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/5/prime-react-tree-db', $this->headers)
            ->assertStatus(401);
    }

    public function testApiConfConnectorFromOrganization2PrimeReactTreeDbConfConnector()
    {
        $this->assertTrue($this->_login(2, 2));

        $this->get($this->appUrl . '/api/conf-connector/1/prime-react-tree-db', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/2/prime-react-tree-db', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.0.key', 'test-db')
                    ->count('data.0.children', 11)
                    ->etc()
            );

        $this->get($this->appUrl . '/api/conf-connector/3/prime-react-tree-db', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/5/prime-react-tree-db', $this->headers)
            ->assertStatus(200);
    }

    public function testApiConfConnectorFromOrganization3PrimeReactTreeDbConfConnector()
    {
        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/conf-connector/1/prime-react-tree-db', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/2/prime-react-tree-db', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/3/prime-react-tree-db', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/conf-connector/5/prime-react-tree-db', $this->headers)
            ->assertStatus(401);
    }

    public function testApiConfConnectorFromOrganization1Crud()
    {
        $this->assertTrue($this->_login(1, 1));

        // ConfConnector from Organization 1
        $response = $this->postConfConnector()
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Local connexion')
                    ->where('data.organization_id', 1)
                    ->etc()
            );

        /** @var Directory $directory */
        $confConnector = $response->original['data']->resource;

        $this->putConfConnector($confConnector->id)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Local connexion updated')
                    ->where('data.organization_id', 1)
                    ->where('data.use_ssl', true)
                    ->etc()
            );

        $this->deleteConfConnector($confConnector->id)
            ->assertStatus(200);

        // ConfConnector from Organization 2
        $this->postConfConnector(2)
            ->assertStatus(401);

        $this->putConfConnector(2, 2)
            ->assertStatus(401);

        $this->deleteConfConnector(2)
            ->assertStatus(401);

        // ConfConnector from Organization 3
        $this->postConfConnector(3)
            ->assertStatus(401);

        $this->putConfConnector(3, 3)
            ->assertStatus(401);

        $this->deleteConfConnector(3)
            ->assertStatus(401);
    }

    public function testApiConfConnectorFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2));

        // ConfConnector from Organization 1
        $this->postConfConnector()
            ->assertStatus(401);

        $this->putConfConnector(1)
            ->assertStatus(401);

        $this->deleteConfConnector(1)
            ->assertStatus(401);

        // ConfConnector from Organization 2
        $this->postConfConnector(2)
            ->assertStatus(401);

        $this->putConfConnector(2, 2)
            ->assertStatus(401);

        $this->deleteConfConnector(2)
            ->assertStatus(401);

        // ConfConnector from Organization 3
        $this->postConfConnector(3)
            ->assertStatus(401);

        $this->putConfConnector(3, 3)
            ->assertStatus(401);

        $this->deleteConfConnector(3)
            ->assertStatus(401);
    }

    public function testApiConfConnectorFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));

        // ConfConnector from Organization 1
        $this->postConfConnector()
            ->assertStatus(401);

        $this->putConfConnector(1)
            ->assertStatus(401);

        $this->deleteConfConnector(1)
            ->assertStatus(401);

        // ConfConnector from Organization 2
        $this->postConfConnector(2)
            ->assertStatus(401);

        $this->putConfConnector(2, 2)
            ->assertStatus(401);

        $this->deleteConfConnector(2)
            ->assertStatus(401);

        // ConfConnector from Organization 3
        $this->postConfConnector(3)
            ->assertStatus(401);

        $this->putConfConnector(3, 3)
            ->assertStatus(401);

        $this->deleteConfConnector(3)
            ->assertStatus(401);
    }

    //
    // Directory
    //
    public function testApiDirectoryFromOrganization1DirectoriesListing()
    {
        $this->assertTrue($this->_login());

        $this->get($this->appUrl . '/api/directory', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 13)
                    ->etc()
            );
    }

    public function testApiDirectoryFromOrganization2DirectoriesListing()
    {
        $this->assertTrue($this->_login(2, 2));

        $this->get($this->appUrl . '/api/directory', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 2)
                    ->etc()
            );
    }

    public function testApiDirectoryFromOrganization3DirectoriesListing()
    {

        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/directory', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 4)
                    ->etc()
            );
    }

    public function testApiDirectoryFromOrganization1DirectoriesShow()
    {
        $this->assertTrue($this->_login());

        // Directory from Organization 1
        $this->get($this->appUrl . '/api/directory/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/directory/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/directory/3', $this->headers)
            ->assertStatus(200);

        // Directory from Organization 2
        $this->get($this->appUrl . '/api/directory/20', $this->headers)
            ->assertStatus(401);

        // Directory from Organization 3
        $this->get($this->appUrl . '/api/directory/32', $this->headers)
            ->assertStatus(401);
    }

    public function testApiDirectoryFromOrganization2DirectoriesShow()
    {
        $this->assertTrue($this->_login(2, 2));

        // Directory from Organization 1
        $this->get($this->appUrl . '/api/directory/1', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/directory/2', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/directory/3', $this->headers)
            ->assertStatus(401);

        // Directory from Organization 2
        $this->get($this->appUrl . '/api/directory/20', $this->headers)
            ->assertStatus(200);

        // Directory from Organization 3
        $this->get($this->appUrl . '/api/directory/32', $this->headers)
            ->assertStatus(401);
    }

    public function testApiDirectoryFromOrganization3DirectoriesShow()
    {
        $this->assertTrue($this->_login(3, 3));

        // Inside Organization 1
        $this->get($this->appUrl . '/api/directory/1', $this->headers)
            ->assertStatus(401);
        $this->get($this->appUrl . '/api/directory/2', $this->headers)
            ->assertStatus(401);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/directory/20', $this->headers)
            ->assertStatus(401);

        // Inside Organization 3
        $this->get($this->appUrl . '/api/directory/32', $this->headers)
            ->assertStatus(200);
    }

    public function testApiDirectoryFromOrganization1Crud()
    {
        $this->assertTrue($this->_login());

        // Directory from Organization 1
        $response = $this->postDirectory()
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Directory')
                    ->where('data.organization_id', 1)
                    ->etc()
            );

        /** @var Directory $directory */
        $directory = $response->original['data']->resource;

        $this->putDirectory($directory->id)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Directory Updated')
                    ->where('data.organization_id', 1)
                    ->etc()
            );

        $this->deleteDirectory($directory->id)
            ->assertStatus(200);

        // Directory from Organization 2
        $this->postDirectory(2)
            ->assertStatus(401);

        $this->putDirectory(20, 2)
            ->assertStatus(401);

        $this->deleteDirectory(20)
            ->assertStatus(401);

        // Directory from Organization 3
        $this->postDirectory(3, 30)
            ->assertStatus(401);

        $this->putDirectory(32, 3, 30)
            ->assertStatus(401);

        $this->deleteDirectory(32)
            ->assertStatus(401);
    }

    public function testApiDirectoryFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2));

        // Directory from Organization 2
        $response = $this->postDirectory(2, 20)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Directory')
                    ->where('data.organization_id', 2)
                    ->etc()
            );

        /** @var Directory $directory */
        $directory = $response->original['data']->resource;

        $this->putDirectory($directory->id, 2, 20)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Directory Updated')
                    ->where('data.organization_id', 2)
                    ->etc()
            );

        $this->deleteDirectory($directory->id)
            ->assertStatus(200);

        // Directory from Organization 1
        $this->postDirectory()
            ->assertStatus(401);

        $this->putDirectory(7)
            ->assertStatus(401);

        $this->deleteDirectory(7)
            ->assertStatus(401);

        // Directory from Organization 3
        $this->postDirectory(3, 30)
            ->assertStatus(401);

        $this->putDirectory(32, 3, 30)
            ->assertStatus(401);

        $this->deleteDirectory(32)
            ->assertStatus(401);
    }

    public function testApiDirectoryFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));

        // Directory from Organization 3
        $this->postDirectory(3, 32)
            ->assertStatus(401);

        $this->putDirectory(33, 3, 31)
            ->assertStatus(401);

        $this->deleteDirectory(33)
            ->assertStatus(401);

        // Directory from Organization 1
        $this->postDirectory()
            ->assertStatus(401);

        $this->putDirectory(7)
            ->assertStatus(401);

        $this->deleteDirectory(7)
            ->assertStatus(401);

        // Directory from Organization 2
        $this->postDirectory(2, 20)
            ->assertStatus(401);

        $this->putDirectory(21, 2)
            ->assertStatus(401);

        $this->deleteDirectory(21)
            ->assertStatus(401);
    }

    //
    // Group
    //
    public function testApiGroupFromOrganization1Listing()
    {
        $this->assertTrue($this->_login());

        $this->get($this->appUrl . '/api/group', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 3)
                    ->etc()
            );
    }

    public function testApGroup2FromOrganizationListing()
    {
        $this->assertTrue($this->_login(2, 2));

        $this->get($this->appUrl . '/api/group', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 3)
                    ->etc()
            );
    }

    public function testApiGroupFromOrganization3Listing()
    {
        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/group', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 3)
                    ->etc()
            );
    }

    public function testApiGroupFromOrganization1Show()
    {
        $this->assertTrue($this->_login());

        // Group from Organization 1
        $this->get($this->appUrl . '/api/group/1', $this->headers)
            ->assertStatus(200);

        // Group from Organization 2
        $this->get($this->appUrl . '/api/group/4', $this->headers)
            ->assertStatus(401);

        // Group from Organization 3
        $this->get($this->appUrl . '/api/group/7', $this->headers)
            ->assertStatus(401);
    }

    public function testApiGroupFromOrganization2Show()
    {
        $this->assertTrue($this->_login(2, 2));

        // Group from Organization 1
        $this->get($this->appUrl . '/api/group/1', $this->headers)
            ->assertStatus(401);

        // Group from Organization 2
        $this->get($this->appUrl . '/api/group/4', $this->headers)
            ->assertStatus(200);

        // Group from Organization 3
        $this->get($this->appUrl . '/api/group/7', $this->headers)
            ->assertStatus(401);
    }

    public function testApiGroupFromOrganization3Show()
    {
        $this->assertTrue($this->_login(3, 3));

        // Group from Organization 1
        $this->get($this->appUrl . '/api/group/1', $this->headers)
            ->assertStatus(401);

        // Group from Organization 2
        $this->get($this->appUrl . '/api/group/4', $this->headers)
            ->assertStatus(401);

        // Group from Organization 3
        $this->get($this->appUrl . '/api/group/7', $this->headers)
            ->assertStatus(200);
    }

    public function testApiGroupFromOrganization1Crud()
    {
        $this->assertTrue($this->_login());

        // Group from Organization 1
        $response = $this->postGroup()
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Group')
                    ->where('data.organization_id', 1)
                    ->etc()
            );

        /** @var Group $group */
        $group = $response->original['data']->resource;

        $this->putGroup($group->id)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Group Updated')
                    ->where('data.organization_id', 1)
                    ->etc()
            );

        $this->deleteGroup($group->id)
            ->assertStatus(200);

        // Group from Organization 2
        $this->postGroup(2)
            ->assertStatus(401);

        $this->putGroup(4, 2)
            ->assertStatus(401);

        $this->deleteGroup(4)
            ->assertStatus(401);

        // Group from Organization 3
        $this->postGroup(3)
            ->assertStatus(401);

        $this->putGroup(7, 3)
            ->assertStatus(401);

        $this->deleteGroup(7)
            ->assertStatus(401);
    }

    public function testApiGroupFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2));

        // Group from Organization 2
        $response = $this->postGroup(2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Group')
                    ->where('data.organization_id', 2)
                    ->etc()
            );

        /** @var Group $group */
        $group = $response->original['data']->resource;

        $this->putGroup($group->id, 2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Group Updated')
                    ->where('data.organization_id', 2)
                    ->etc()
            );

        $this->deleteGroup($group->id)
            ->assertStatus(200);

        // Group from Organization 1
        $this->postGroup()
            ->assertStatus(401);

        $this->putGroup(1)
            ->assertStatus(401);

        $this->deleteGroup(1)
            ->assertStatus(401);

        // Group from Organization 3
        $this->postGroup(3)
            ->assertStatus(401);

        $this->putGroup(7, 3)
            ->assertStatus(401);

        $this->deleteGroup(7)
            ->assertStatus(401);
    }

    public function testApiGroupFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));

        // Group from Organization 3
        $this->postGroup(3)
            ->assertStatus(401);

        $this->putGroup(7, 3)
            ->assertStatus(401);

        $this->deleteGroup(7)
            ->assertStatus(401);

        // Group from Organization 1
        $this->postGroup()
            ->assertStatus(401);

        $this->putGroup(1)
            ->assertStatus(401);

        $this->deleteGroup(1)
            ->assertStatus(401);

        // Group from Organization 2
        $this->postGroup(2)
            ->assertStatus(401);

        $this->putGroup(4, 2)
            ->assertStatus(401);

        $this->deleteGroup(4)
            ->assertStatus(401);
    }

    //
    // Report
    //
    public function testApiReportFromOrganization1ReportsListing()
    {
        $this->assertTrue($this->_login());
        $this->get($this->appUrl . '/api/report', $this->headers)
            ->assertStatus(200)
            // Reports from organization 1
            ->assertJsonFragment(['name' => 'Report 3'])
            ->assertJsonFragment(['name' => 'Report 4'])
            ->assertJsonFragment(['name' => 'Report 5'])
            ->assertJsonFragment(['name' => 'Report 6'])
            ->assertJsonFragment(['name' => 'Report 7'])
            ->assertJsonFragment(['name' => 'Report 8'])
            ->assertJsonFragment(['name' => 'Report 9'])
            ->assertJsonFragment(['name' => 'Report 10'])
            ->assertJsonFragment(['name' => 'Report 11'])
            ->assertJsonFragment(['name' => 'Report 12'])
            ->assertJsonFragment(['name' => 'Report 14'])
            ->assertJsonFragment(['name' => 'Report 15'])
            ->assertJsonFragment(['name' => 'Report 18'])
            // Reports from organization 2 - should not be here at all
            ->assertJsonMissing(['name' => 'Report 20'])
            ->assertJsonMissing(['name' => 'Report 21']);
    }

    public function testApiReportFromOrganization2ReportsListing()
    {
        $this->assertTrue($this->_login(2, 2));
        $this->get($this->appUrl . '/api/report', $this->headers)
            ->assertStatus(200)
            // Reports from organization 1 - should not be here at all.
            ->assertJsonMissing(['name' => 'Report 3'])
            ->assertJsonMissing(['name' => 'Report 4'])
            ->assertJsonMissing(['name' => 'Report 5'])
            ->assertJsonMissing(['name' => 'Report 6'])
            ->assertJsonMissing(['name' => 'Report 7'])
            ->assertJsonMissing(['name' => 'Report 8'])
            ->assertJsonMissing(['name' => 'Report 9'])
            ->assertJsonMissing(['name' => 'Report 10'])
            ->assertJsonMissing(['name' => 'Report 11'])
            ->assertJsonMissing(['name' => 'Report 12'])
            ->assertJsonMissing(['name' => 'Report 14'])
            ->assertJsonMissing(['name' => 'Report 15'])
            ->assertJsonMissing(['name' => 'Report 18'])
            // Reports from organization 2
            ->assertJsonFragment(['name' => 'Report 20'])
            ->assertJsonFragment(['name' => 'Report 21']);
    }

    public function testApiReportFromOrganization3ReportsListing()
    {
        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/report', $this->headers)
            ->assertStatus(200)
            // Reports from organization 1 - should not be here at all.
            ->assertJsonMissing(['name' => 'Report 3'])
            ->assertJsonMissing(['name' => 'Report 4'])
            ->assertJsonMissing(['name' => 'Report 5'])
            ->assertJsonMissing(['name' => 'Report 6'])
            ->assertJsonMissing(['name' => 'Report 7'])
            ->assertJsonMissing(['name' => 'Report 8'])
            ->assertJsonMissing(['name' => 'Report 9'])
            ->assertJsonMissing(['name' => 'Report 10'])
            ->assertJsonMissing(['name' => 'Report 11'])
            ->assertJsonMissing(['name' => 'Report 12'])
            ->assertJsonMissing(['name' => 'Report 14'])
            ->assertJsonMissing(['name' => 'Report 15'])
            ->assertJsonMissing(['name' => 'Report 18'])
            // Reports from organization 2 - should not be here at all.
            ->assertJsonMissing(['name' => 'Report 20'])
            ->assertJsonMissing(['name' => 'Report 21']);
    }

    public function testApiReportFromOrganization1ReportsShow()
    {
        $this->assertTrue($this->_login());

        // Report from Organization 1
        $this->get($this->appUrl . '/api/report/1', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report/1/run', [], $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/report/3', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report/3/run', [], $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/report/5', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report/5/run', [], $this->headers)
            ->assertStatus(200);

        // Report from Organization 2
        $this->get($this->appUrl . '/api/report/19', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/19/run', [], $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report/20', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/20/run', [], $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report/21', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/21/run', [], $this->headers)
            ->assertStatus(401);
    }

    public function testApiReportFromOrganization2ReportsShow()
    {
        $this->assertTrue($this->_login(2, 2));
        // Report from Organization 1
        $this->get($this->appUrl . '/api/report/1', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/1/run', [], $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report/3', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/3/run', [], $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report/5', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/5/run', [], $this->headers)
            ->assertStatus(401);

        // Report from Organization 2
        $this->get($this->appUrl . '/api/report/19', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report/19/run', [], $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/report/20', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report/20/run', [], $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/report/21', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report/21/run', [], $this->headers)
            ->assertStatus(200);
    }

    public function testApiReportFromOrganization3ReportsShow()
    {
        $this->assertTrue($this->_login(3, 3));

        // Report from Organization 1
        $this->get($this->appUrl . '/api/report/1', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/1/run', [], $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report/3', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/3/run', [], $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report/5', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/5/run', [], $this->headers)
            ->assertStatus(401);

        // Report from Organization 2
        $this->get($this->appUrl . '/api/report/19', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/19/run', [], $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report/20', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/20/run', [], $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report/21', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report/21/run', [], $this->headers)
            ->assertStatus(401);
    }

    public function testApiReportFromOrganization1Crud()
    {
        $this->assertTrue($this->_login());

        // Report from Organization 1
        $response = $this->postReport()
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.user_id', 2)
                    ->has('data.id')
                    ->has('data.allowed_groups')
                    ->count('data.allowed_groups', 2)
                    ->has('data.allowed_users')
                    ->count('data.allowed_users', 2)
                    ->etc()
            );

        /** @var Report $report */
        $report = $response->original['data']->resource;

        $this->putReport(1, $report->id)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Report ' . $report->id . ' - modified')
                    ->has('data.allowed_groups')
                    ->count('data.allowed_groups', 1)
                    ->has('data.allowed_users')
                    ->count('data.allowed_users', 1)
                    ->etc()
            );

        $this->deleteReport($report->id)
            ->assertStatus(200);

        $this->putReportUpdateQueries(2, 'glopi', 'glopa')
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.query_init', 'glopi')
                    ->where('data.query_cleanup', 'glopa')
                    ->etc()
            );

        $this->putReportUpdateQueries(1)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.query_init', null)
                    ->where('data.query_cleanup', null)
                    ->etc()
            );


        // Report from inside Organization 2
        $this->postReport(2, 2)
            ->assertStatus(401);

        $this->putReport(2, 19, 2)
            ->assertStatus(401);

        $this->deleteReport(19)
            ->assertStatus(401);

        $this->putReportUpdateQueries(19, 'glopi', 'glopa')
            ->assertStatus(401);

        // Report from inside Organization 3
        $this->postReport(3, 3)
            ->assertStatus(401);
    }

    public function testApiReportFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2));

        // Report from Organization 2
        $response = $this->postReport(2, 2, 4, 20, [4, 5])
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.user_id', 2)
                    ->has('data.id')
                    ->has('data.allowed_groups')
                    ->count('data.allowed_groups', 2)
                    ->has('data.allowed_users')
                    ->count('data.allowed_users', 2)
                    ->etc()
            );

        /** @var Report $report */
        $report = $response->original['data']->resource;

        $this->putReport(2, $report->id, 2, 4, 20, [4], [3])
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Report ' . $report->id . ' - modified')
                    ->has('data.allowed_groups')
                    ->count('data.allowed_groups', 1)
                    ->has('data.allowed_users')
                    ->count('data.allowed_users', 1)
                    ->etc()
            );

        $this->deleteReport($report->id)
            ->assertStatus(200);

        $this->putReportUpdateQueries(19, 'glopi', 'glopa')
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.query_init', 'glopi')
                    ->where('data.query_cleanup', 'glopa')
                    ->etc()
            );

        $this->putReportUpdateQueries(19)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.query_init', null)
                    ->where('data.query_cleanup', null)
                    ->etc()
            );

        // Report from Organization 1
        $this->postReport()
            ->assertStatus(401);

        $this->putReport(1, 1)
            ->assertStatus(401);

        $this->deleteReport(2)
            ->assertStatus(401);

        $this->putReportUpdateQueries(2, 'glopi', 'glopa')
            ->assertStatus(401);
    }

    public function testApiReportFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));
        $this->postReport(3, 3)
            ->assertStatus(401);
    }

    //
    // Report Data View
    //
    public function testApiReportDataViewFromOrganization1DataViewsListing()
    {
        $this->assertTrue($this->_login());
        $this->get($this->appUrl . '/api/report-data-view/', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 4)
                    ->etc()
            );
    }

    public function testApiReportDataViewFromOrganization2DataViewsListing()
    {
        $this->assertTrue($this->_login(2, 2));
        $this->get($this->appUrl . '/api/report-data-view/', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 1)
                    ->etc()
            );
    }

    public function testApiReportDataViewFromOrganization3DataViewsListing()
    {
        $this->assertTrue($this->_login(3, 3));
        $this->get($this->appUrl . '/api/report-data-view/', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 1)
                    ->etc()
            );
    }

    public function testApiReportDataViewFromOrganization1DataViewsShow()
    {
        $this->assertTrue($this->_login());

        // ReportDataView from Organization 1
        $this->get($this->appUrl . '/api/report-data-view/1', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report-data-view/1/run', $this->headers)
            ->assertStatus(200);

        // ReportDataView from Organization 2
        $this->get($this->appUrl . '/api/report-data-view/4', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report-data-view/4/run', $this->headers)
            ->assertStatus(401);

        // Parent Report not visible for VIEWERs.
        $this->get($this->appUrl . '/api/report-data-view/5', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report-data-view/5/run', $this->headers)
            ->assertStatus(200);
    }

    public function testApiReportDataViewFromOrganization2DataViewsShow()
    {
        $this->assertTrue($this->_login(2, 2));

        // Inside Organization 2
        $this->get($this->appUrl . '/api/report-data-view/4', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report-data-view/4/run', $this->headers)
            ->assertStatus(200);

        // Inside Organization 1
        $this->get($this->appUrl . '/api/report-data-view/1', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report-data-view/1/run', $this->headers)
            ->assertStatus(401);

        // Parent Report not visible for VIEWERs.
        $this->get($this->appUrl . '/api/report-data-view/5', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report-data-view/5/run', $this->headers)
            ->assertStatus(401);
    }

    public function testApiReportDataViewFromOrganization3DataViewsShow()
    {
        $this->assertTrue($this->_login(2, 2));

        // Inside Organization 1
        $this->get($this->appUrl . '/api/report-data-view/1', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report-data-view/1/run', $this->headers)
            ->assertStatus(401);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/report-data-view/1', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report-data-view/1/run', $this->headers)
            ->assertStatus(401);

        // Parent Report not visible for VIEWERs.
        $this->get($this->appUrl . '/api/report-data-view/6', $this->headers)
            ->assertStatus(401);
        $this->post($this->appUrl . '/api/report-data-view/6/run', $this->headers)
            ->assertStatus(401);
    }

    public function testApiReportDataViewFromOrganization1Crud()
    {
        $this->assertTrue($this->_login());

        // ReportDataView from Organization 1
        $response = $this->postReportDataView(2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.type', 2)
                    ->etc()
            );

        /** @var ReportDataView $reportDataView */
        $reportDataView = $response->original['data']->resource;

        $this->putReportDataView($reportDataView->id, 2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.title', 'Data view for report 2 - modified')
                    ->etc()
            );

        $this->deleteReportDataView($reportDataView->id)
            ->assertStatus(200);

        $this->putReportDataViewUpdateQuery(3, 1, 'glopi - modified')
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.query', 'glopi - modified')
                    ->etc()
            );

        // ReportDataView from Organization 2
        $this->postReportDataView(19)
            ->assertStatus(401);

        $this->putReportDataView(4, 19)
            ->assertStatus(401);

        $this->deleteReportDataView(4)
            ->assertStatus(401);

        $this->putReportDataViewUpdateQuery(4, 19, 'glopi')
            ->assertStatus(401);

        // ReportDataView from Organization 3
        $this->postReportDataView(22)
            ->assertStatus(401);

        $this->putReportDataView(6, 22)
            ->assertStatus(401);

        $this->deleteReportDataView(6)
            ->assertStatus(401);

        $this->putReportDataViewUpdateQuery(6, 22, 'glopi')
            ->assertStatus(401);
    }

    public function testApiReportDataViewFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2));

        // ReportDataView fromOrganization 2
        $response = $this->postReportDataView(19)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.type', 2)
                    ->etc()
            );

        /** @var ReportDataView $reportDataView */
        $reportDataView = $response->original['data']->resource;

        $this->putReportDataView($reportDataView->id, 19)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.title', 'Data view for report 19 - modified')
                    ->etc()
            );

        $this->deleteReportDataView($reportDataView->id)
            ->assertStatus(200);

        $this->putReportDataViewUpdateQuery(4, 19, 'glopi - modified')
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.query', 'glopi - modified')
                    ->etc()
            );


        // ReportDataView from Organization 1
        $this->postReportDataView()
            ->assertStatus(401);

        $this->putReportDataView(1, 1)
            ->assertStatus(401);

        $this->deleteReportDataView(1)
            ->assertStatus(401);

        $this->putReportDataViewUpdateQuery(1, 1, 'glopi')
            ->assertStatus(401);

        // ReportDataView from Organization 3
        $this->postReportDataView(22)
            ->assertStatus(401);

        $this->putReportDataView(6, 22)
            ->assertStatus(401);

        $this->deleteReportDataView(6)
            ->assertStatus(401);

        $this->putReportDataViewUpdateQuery(6, 22, 'glopi')
            ->assertStatus(401);
    }

    public function testApiReportDataViewFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));

        // ReportDataView from Organization 3
        $this->postReportDataView(22)
            ->assertStatus(401);

        $this->putReportDataView(6, 22)
            ->assertStatus(401);

        $this->deleteReportDataView(6)
            ->assertStatus(401);

        $this->putReportDataViewUpdateQuery(6, 22, 'glopi')
            ->assertStatus(401);

        // ReportDataView from Organization 1
        $this->postReportDataView()
            ->assertStatus(401);

        $this->putReportDataView(1, 1)
            ->assertStatus(401);

        $this->deleteReportDataView(1)
            ->assertStatus(401);

        // Inside Organization 2
        $this->postReportDataView(19)
            ->assertStatus(401);

        $this->putReportDataView(4, 19)
            ->assertStatus(401);

        $this->deleteReportDataView(4)
            ->assertStatus(401);

        $this->putReportDataViewUpdateQuery(4, 19, 'glopi')
            ->assertStatus(401);
    }


    //
    // Report Parameter Input Value
    //
    public function testApiReportParameterInputFromOrganization1CategoriesListing()
    {
        $this->assertTrue($this->_login());

        $this->get($this->appUrl . '/api/report-parameter-input', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 6)
                    ->etc()
            );
    }

    public function testApiReportParameterInputFromOrganization2CategoriesListing()
    {
        $this->assertTrue($this->_login(2, 2));

        $this->get($this->appUrl . '/api/report-parameter-input', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 5)
                    ->etc()
            );
    }

    public function testApiReportParameterInputFromOrganization3CategoriesListing()
    {
        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/report-parameter-input', $this->headers)
            ->assertStatus(401);
    }

    public function testApiReportParameterInputFromOrganization1CategoriesShow()
    {
        $this->assertTrue($this->_login());

        // Report Parameter Input from Organization 1
        $this->get($this->appUrl . '/api/report-parameter-input/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/report-parameter-input/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/report-parameter-input/3', $this->headers)
            ->assertStatus(200);

        // Report Parameter Input from Organization 2
        $this->get($this->appUrl . '/api/report-parameter-input/8', $this->headers)
            ->assertStatus(401);

        // Report Parameter Input from Organization 3
        $this->get($this->appUrl . '/api/report-parameter-input/12', $this->headers)
            ->assertStatus(401);
    }

    public function testApiReportParameterInputFromOrganization2CategoriesShow()
    {
        $this->assertTrue($this->_login(2, 2));

        // Report Parameter Input from Organization 1
        $this->get($this->appUrl . '/api/report-parameter-input/1', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report-parameter-input/2', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report-parameter-input/3', $this->headers)
            ->assertStatus(401);

        // Report Parameter Input from Organization 2
        $this->get($this->appUrl . '/api/report-parameter-input/8', $this->headers)
            ->assertStatus(200);

        // Report Parameter Input from Organization 3
        $this->get($this->appUrl . '/api/report-parameter-input/12', $this->headers)
            ->assertStatus(401);
    }

    public function testApiReportParameterInputFromOrganization3CategoriesShow()
    {
        $this->assertTrue($this->_login(3, 3));

        // Report Parameter Input from Organization 1
        $this->get($this->appUrl . '/api/report-parameter-input/1', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report-parameter-input/2', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/report-parameter-input/3', $this->headers)
            ->assertStatus(401);

        // Report Parameter Input from Organization 2
        $this->get($this->appUrl . '/api/report-parameter-input/8', $this->headers)
            ->assertStatus(401);

        // Report Parameter Input from Organization 3
        $this->get($this->appUrl . '/api/report-parameter-input/12', $this->headers)
            ->assertStatus(401);
    }

    public function testApiReportParameterInputFromOrganization1Crud()
    {
        $this->assertTrue($this->_login());

        // Report Parameter Input from Organization 1
        $response = $this->postReportParameterInput()
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Country')
                    ->where('data.conf_connector_id', 1)
                    ->where('data.default_value', '74')
                    ->etc()
            );

        /** @var ReportParameterInput $reportParameterInput */
        $reportParameterInput = $response->original['data']->resource;

        $this->putReportParameterInput($reportParameterInput->id)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Country modified')
                    ->where('data.conf_connector_id', 1)
                    ->where('data.default_value', '13')
                    ->etc()
            );

        $this->deleteReportParameterInput($reportParameterInput->id)
            ->assertStatus(200);

        // Report Parameter Input from Organization 2
        $this->postReportParameterInput(2)
            ->assertStatus(401);

        $this->putReportParameterInput(7, 2)
            ->assertStatus(401);

        $this->deleteReportParameterInput(7)
            ->assertStatus(401);

        // Report Parameter Input from Organization 3
        $this->postReportParameterInput(3)
            ->assertStatus(401);

        $this->putReportParameterInput(12, 3)
            ->assertStatus(401);

        $this->deleteReportParameterInput(12)
            ->assertStatus(401);
    }

    public function testApiReportParameterInputFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2));

        // Report Parameter Input from Organization 2
        $response = $this->postReportParameterInput(2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Country')
                    ->where('data.conf_connector_id', 2)
                    ->where('data.default_value', '74')
                    ->etc()
            );

        /** @var ReportParameterInput $reportParameterInput */
        $reportParameterInput = $response->original['data']->resource;

        $this->putReportParameterInput($reportParameterInput->id, 2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Country modified')
                    ->where('data.conf_connector_id', 2)
                    ->where('data.default_value', '13')
                    ->etc()
            );

        $this->deleteReportParameterInput($reportParameterInput->id)
            ->assertStatus(200);

        // Report Parameter Input from Organization 1
        $this->postReportParameterInput(1)
            ->assertStatus(401);

        $this->putReportParameterInput(1, 1)
            ->assertStatus(401);

        $this->deleteReportParameterInput(1)
            ->assertStatus(401);

        // Report Parameter Input from Organization 3
        $this->postReportParameterInput(3)
            ->assertStatus(401);

        $this->putReportParameterInput(12, 3)
            ->assertStatus(401);

        $this->deleteReportParameterInput(12)
            ->assertStatus(401);
    }

    public function testApiReportParameterInputFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));

        // Report Parameter Input from Organization 3
        $this->postReportParameterInput(3)
            ->assertStatus(401);

        $this->putReportParameterInput(12, 3)
            ->assertStatus(401);

        $this->deleteReportParameterInput(12)
            ->assertStatus(401);

        // Report Parameter Input from Organization 1
        $this->postReportParameterInput(1)
            ->assertStatus(401);

        $this->putReportParameterInput(1, 1)
            ->assertStatus(401);

        $this->deleteReportParameterInput(1)
            ->assertStatus(401);

        // Report Parameter Input from Organization 2
        $this->postReportParameterInput(2)
            ->assertStatus(401);

        $this->putReportParameterInput(7, 2)
            ->assertStatus(401);

        $this->deleteReportParameterInput(7)
            ->assertStatus(401);
    }

    //
    // Role
    //

    //
    // User
    //
    public function testApiUserFromOrganization1UsersListing()
    {
        $this->assertTrue($this->_login());

        $this->get($this->appUrl . '/api/user', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 7)
                    ->etc()
            );
    }

    public function testApiUserFromOrganization2UsersListing()
    {
        $this->assertTrue($this->_login(2, 2));
        // Inside Organization 2
        $this->get($this->appUrl . '/api/user', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 5)
                    ->etc()
            );
    }

    public function testApiUserFromOrganization3UsersListing()
    {
        $this->assertTrue($this->_login(3, 3));
        // Inside Organization 3
        $this->get($this->appUrl . '/api/user', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 3)
                    ->etc()
            );
    }

    public function testApiUserFromOrganization1UsersShow()
    {
        $this->assertTrue($this->_login());

        $this->get($this->appUrl . '/api/user/1', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/3', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/4', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/5', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/8', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/6', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/6', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/7', $this->headers)
            ->assertStatus(401);
    }

    public function testApiUserFromOrganization2UsersShow()
    {
        $this->assertTrue($this->_login(2, 2));

        $this->get($this->appUrl . '/api/user/1', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/3', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/4', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/5', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/6', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/9', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/7', $this->headers)
            ->assertStatus(401);
    }

    public function testApiUserFromOrganization3UsersShow()
    {
        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/user/1', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/3', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/4', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/5', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/6', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/9', $this->headers)
            ->assertStatus(401);

        $this->get($this->appUrl . '/api/user/7', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/10', $this->headers)
            ->assertStatus(401);
    }

    public function testApiUserFromOrganization1Crud()
    {
        $this->assertTrue($this->_login());

        // User from Organization 1
        $random_name = Str::random(5);

        $response = $this->postUser($random_name)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.email', $random_name . '@atomicweb.fr')
                    ->has('data.organization_users')
                    ->count('data.organization_users', 1)
                    ->has('data.organization_users.0.groups')
                    ->count('data.organization_users.0.groups', 2)
                    ->where('data.organization_users.0.groups.0.id', 1)
                    ->count('data.organization_users.0.roles', 1)
                    ->where('data.organization_users.0.roles.0.id', 2)
                    ->etc()
            );

        /** @var User $user */
        $user = $response->original['data']->resource;

        $this->putUser($random_name, $user->id)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.email', $random_name . '@atomicweb.fr')
                    ->has('data.organization_users')
                    ->count('data.organization_users', 1)
                    ->has('data.organization_users.0.groups')
                    ->count('data.organization_users.0.groups', 1)
                    ->where('data.organization_users.0.groups.0.id', 2)
                    ->count('data.organization_users.0.roles', 2)
                    ->where('data.organization_users.0.roles.0.id', 1)
                    ->etc()
            );

        $this->deleteUser($user->id)
            ->assertStatus(200);

        // User from Organization 2
        $this->postUser($random_name, 2)
            ->assertStatus(401);

        $this->putUser($random_name, 9, 2)
            ->assertStatus(401);

        // User from Organization 3
        $this->postUser($random_name, 3)
            ->assertStatus(401);

        $this->putUser($random_name, 10, 3)
            ->assertStatus(401);

        // Super admin - do not touch
        $this->putUser($random_name, 1)
            ->assertStatus(401);

        $this->deleteUser(1)
            ->assertStatus(401);
    }

    public function testApiUserFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2));

        // User from Organization 2
        $random_name = Str::random(5);

        $response = $this->postUser($random_name, 2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.email', $random_name . '@atomicweb.fr')
                    ->has('data.organization_users')
                    ->count('data.organization_users', 1)
                    ->has('data.organization_users.0.groups')
                    ->count('data.organization_users.0.groups', 2)
                    ->where('data.organization_users.0.groups.0.id', 1)
                    ->count('data.organization_users.0.roles', 1)
                    ->where('data.organization_users.0.roles.0.id', 2)
                    ->etc()
            );

        /** @var User $user */
        $user = $response->original['data']->resource;

        $this->putUser($random_name, $user->id, 2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.email', $random_name . '@atomicweb.fr')
                    ->has('data.organization_users')
                    ->count('data.organization_users', 1)
                    ->has('data.organization_users.0.groups')
                    ->count('data.organization_users.0.groups', 1)
                    ->where('data.organization_users.0.groups.0.id', 2)
                    ->count('data.organization_users.0.roles', 2)
                    ->where('data.organization_users.0.roles.0.id', 1)
                    ->etc()
            );

        $this->deleteUser($user->id)
            ->assertStatus(200);

        // User from Organization 1
        $this->postUser($random_name)
            ->assertStatus(401);

        $this->putUser($random_name, 3)
            ->assertStatus(401);

        // User from Organization 3
        $this->postUser($random_name, 3)
            ->assertStatus(401);

        $this->putUser($random_name, 10, 3)
            ->assertStatus(401);

        // Super admin - do not touch
        $this->putUser($random_name, 1)
            ->assertStatus(401);

        $this->deleteUser(1)
            ->assertStatus(401);
    }

    public function testApiUserFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));

        // User from Organization 3
        $random_name = Str::random(5);

        $this->postUser($random_name, 3)
            ->assertStatus(401);

        $this->putUser($random_name, 10, 3)
            ->assertStatus(401);

        $this->deleteUser(10)
            ->assertStatus(401);

        // User from Organization 1
        $this->postUser($random_name)
            ->assertStatus(401);

        $this->putUser($random_name, 3)
            ->assertStatus(401);

        // User from Organization 2
        $this->postUser($random_name, 2)
            ->assertStatus(401);

        $this->putUser($random_name, 9, 2)
            ->assertStatus(401);

        // Super admin - do not touch
        $this->putUser($random_name, 1)
            ->assertStatus(401);

        $this->deleteUser(1)
            ->assertStatus(401);
    }
}
