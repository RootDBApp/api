<?php
/*
 * This file is part of RootDB.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * AUTHORS
 * PORQUET Sébastien <sebastien.porquet@ijaz.fr>
 */

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Directory;
use App\Models\Group;
use App\Models\Report;
use App\Models\ReportDataView;
use App\Models\ReportParameterInput;
use App\Models\User;
use CreateReportParameterInputsTable;
use Database\Seeders\dev\ReportParameterInputsSeeder;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class APIUserSuperAdminTest extends TestCase
{
    private function _login(int $expectedOrganizationId = 1, int $organizationId = 0): bool
    {
        $response = $this->postLogin('super-admin', $organizationId)
            ->assertStatus(200);

        if ($response->status() === 200) {
            $response
                ->assertJson(
                    fn(AssertableJson $json) => $json
                        ->where('data.is_super_admin', true)
                        ->where('data.id', 1)
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

        $this->assertTrue($this->_login(2, 2, 2));
        $this->postLogout()->assertStatus(200);

        $this->assertTrue($this->_login(3, 3, 3));
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
        $this->assertTrue($this->_login(2, 2, 2));

        $this->get($this->appUrl . '/api/category?for-organization-id=1', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 2)
                    ->etc()
            );
    }

    public function testApiCategoryFromOrganization3CategoriesListing()
    {
        $this->assertTrue($this->_login(3, 3, 3));

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

        // Inside Organization 1
        $this->get($this->appUrl . '/api/category/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/category/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/category/3', $this->headers)
            ->assertStatus(200);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/category/4', $this->headers)
            ->assertStatus(200);

        // Inside Organization 3
        $this->get($this->appUrl . '/api/category/6', $this->headers)
            ->assertStatus(200);
    }

    public function testApiCategoryFromOrganization2CategoriesShow()
    {
        $this->assertTrue($this->_login(2, 2, 2));

        // Inside Organization 1
        $this->get($this->appUrl . '/api/category/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/category/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/category/3', $this->headers)
            ->assertStatus(200);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/category/4', $this->headers)
            ->assertStatus(200);

        // Inside Organization 3
        $this->get($this->appUrl . '/api/category/6', $this->headers)
            ->assertStatus(200);
    }

    public function testApiCategoryFromOrganization3CategoriesShow()
    {
        $this->assertTrue($this->_login(3, 3, 3));

        // Inside Organization 1
        $this->get($this->appUrl . '/api/category/1', $this->headers)
            ->assertStatus(200);
        $this->get($this->appUrl . '/api/category/2', $this->headers)
            ->assertStatus(200);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/category/4', $this->headers)
            ->assertStatus(200);

        // Inside Organization 3
        $this->get($this->appUrl . '/api/category/6', $this->headers)
            ->assertStatus(200);
    }

    public function testApiCategoryFromOrganization1Crud()
    {
        $this->assertTrue($this->_login());

        // Inside Organization 1
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

        // Inside Organization 2
        $response = $this->postCategory(2)
            ->assertStatus(200);

        $category = $response->original['data']->resource;

        $this->deleteCategory($category->id)
            ->assertStatus(200);

        $this->putCategory(4, 2)
            ->assertStatus(200);


        // Inside Organization 3
        $response = $this->postCategory(3)
            ->assertStatus(200);

        $category = $response->original['data']->resource;

        $this->deleteCategory($category->id)
            ->assertStatus(200);

        $this->putCategory(6, 3)
            ->assertStatus(200);
    }

    public function testApiCategoryFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3, 3));
        // Inside Organization 1
        $response = $this->postCategory(3)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Category')
                    ->where('data.organization_id', 3)
                    ->etc()
            );

        /** @var Category $category */
        $category = $response->original['data']->resource;

        $this->putCategory($category->id, 3)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Category Updated')
                    ->where('data.organization_id', 3)
                    ->etc()
            );

        $this->deleteCategory($category->id)
            ->assertStatus(200);

        // Inside Organization 1
        $response = $this->postCategory()
            ->assertStatus(200);

        $category = $response->original['data']->resource;

        $this->deleteCategory($category->id)
            ->assertStatus(200);

        $this->putCategory(1)
            ->assertStatus(200);

        // Inside Organization 2
        $response = $this->postCategory(2)
            ->assertStatus(200);

        $category = $response->original['data']->resource;

        $this->deleteCategory($category->id)
            ->assertStatus(200);

        $this->putCategory(4, 2)
            ->assertStatus(200);
    }

    //
    // ConfConnector
    //
    public function testApiConfConnectorFromOrganization1ConfConnectorListing()
    {
        $this->assertTrue($this->_login());

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
            ->assertStatus(200);
    }

    public function testApiConfConnectorFromOrganization1PrimeReactTreeDbConfConnector1()
    {
        $this->assertTrue($this->_login());

        $this->get($this->appUrl . '/api/conf-connector/1/prime-react-tree-db', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.0.key', 'test-db')
                    ->count('data.0.children', 11)
                    ->etc()
            );
    }

    public function testApiConfConnectorFromOrganization1PrimeReactTreeDbConfConnector4()
    {
        $this->assertTrue($this->_login(1, 1));

        $this->get($this->appUrl . '/api/conf-connector/4/prime-react-tree-db', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 1)
                    ->where('data.0.key', 'up-api')
                    ->count('data.0.children', 31)
                    ->etc()
            );
    }

    public function testApiConfConnectorFromOrganization2PrimeReactTreeDbConfConnector2()
    {
        $this->assertTrue($this->_login(2, 2));


        $this->get($this->appUrl . '/api/conf-connector/2/prime-react-tree-db', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.0.key', 'test-db')
                    ->count('data.0.children', 11)
                    ->etc()
            );
    }

    public function testApiConfConnectorFromOrganization2PrimeReactTreeDbConfConnector4()
    {
        $this->assertTrue($this->_login(2, 2));

        $this->get($this->appUrl . '/api/conf-connector/4/prime-react-tree-db', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 1)
                    ->where('data.0.key', 'up-api')
                    ->count('data.0.children', 31)
                    ->etc()
            );
    }

    public function testApiConfConnectorFromOrganization3PrimeReactTreeDbConfConnector3()
    {
        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/conf-connector/3/prime-react-tree-db', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 2)
                    ->where('data.0.key', 'test-db')
                    ->count('data.0.children', 11)
                    ->where('data.1.key', 'wordpress')
                    ->count('data.1.children', 12)
                    ->etc()
            );
    }

    public function testApiConfConnectorFromOrganization3PrimeReactTreeDbConfConnector4()
    {
        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/conf-connector/4/prime-react-tree-db', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 1)
                    ->where('data.0.key', 'up-api')
                    ->count('data.0.children', 31)
                    ->etc()
            );
    }

    //
    // Directory
    //
    public function testApiDirectoryFromOrganization1DirectoriesListing()
    {
        $this->assertTrue($this->_login());

        // Inside Organization 1
        $this->get($this->appUrl . '/api/directory', $this->headers)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 13)
                    ->etc()
            );

        // Inside Organization 2
        $this->get($this->appUrl . '/api/directory?for-organization-id=2', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 2)
                    ->etc()
            );

        // Inside Organization 3
        $this->get($this->appUrl . '/api/directory?for-organization-id=3', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 4)
                    ->etc()
            );
    }

    public function testApiDirectoryFromOrganization2DirectoriesListing()
    {
        $this->assertTrue($this->_login(2, 2, 2));

        // Inside Organization 1
        $this->get($this->appUrl . '/api/directory?for-organization-id=1', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 13)
                    ->etc()
            );

        // Inside Organization 2
        $this->get($this->appUrl . '/api/directory', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 2)
                    ->etc()
            );

        // Inside Organization 3
        $this->get($this->appUrl . '/api/directory?for-organization-id=3', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 4)
                    ->etc()
            );
    }

    public function testApiDirectoryFromOrganization3DirectoriesListing()
    {
        $this->assertTrue($this->_login(3, 3, 3));
        // Inside Organization 1
        $this->get($this->appUrl . '/api/directory?for-organization-id=1', $this->headers)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 13)
                    ->etc()
            );

        // Inside Organization 2
        $this->get($this->appUrl . '/api/directory?for-organization-id=2', $this->headers)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 2)
                    ->etc()
            );

        // Inside Organization 3
        $this->get($this->appUrl . '/api/directory', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 4)
                    ->etc()
            );
    }

    public function testApiDirectoryFromOrganization1DirectoriesAccess()
    {
        $this->assertTrue($this->_login());
        // Inside Organization 1
        $this->get($this->appUrl . '/api/directory/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/directory/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/directory/3', $this->headers)
            ->assertStatus(200);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/directory/20', $this->headers)
            ->assertStatus(200);

        // Inside Organization 3
        $this->get($this->appUrl . '/api/directory/32', $this->headers)
            ->assertStatus(200);
    }

    public function testApiDirectoryFromOrganization2DirectoriesAccess()
    {
        $this->assertTrue($this->_login(2, 2, 2));
        // Inside Organization 1
        $this->get($this->appUrl . '/api/directory/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/directory/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/directory/3', $this->headers)
            ->assertStatus(200);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/directory/20', $this->headers)
            ->assertStatus(200);

        // Inside Organization 3
        $this->get($this->appUrl . '/api/directory/32', $this->headers)
            ->assertStatus(200);
    }

    public function testApiDirectoryFromOrganization3DirectoriesAccess()
    {
        $this->assertTrue($this->_login(3, 3, 3));
        // Inside Organization 1
        $this->get($this->appUrl . '/api/directory/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/directory/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/directory/3', $this->headers)
            ->assertStatus(200);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/directory/20', $this->headers)
            ->assertStatus(200);

        // Inside Organization 3
        $this->get($this->appUrl . '/api/directory/32', $this->headers)
            ->assertStatus(200);
    }

    public function testApiDirectoryFromOrganization1Crud()
    {
        $this->assertTrue($this->_login());
        // Inside Organization 1
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

        // Inside Organization 2
        $response = $this->postDirectory(2, 20)
            ->assertStatus(200);

        /** @var Directory $directory */
        $directory = $response->original['data']->resource;

        $this->putDirectory($directory->id, 2, 20)
            ->assertStatus(200);

        $this->deleteDirectory($directory->id)
            ->assertStatus(200);

        // Inside Organization 3
        $response = $this->postDirectory(3, 30)
            ->assertStatus(200);

        /** @var Directory $directory */
        $directory = $response->original['data']->resource;

        $this->putDirectory($directory->id, 3, 30)
            ->assertStatus(200);

        $this->deleteDirectory($directory->id)
            ->assertStatus(200);
    }

    public function testApiDirectoryFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2, 2));
        // Inside Organization 2
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

        // Inside Organization 1
        $response = $this->postDirectory()
            ->assertStatus(200);

        /** @var Directory $directory */
        $directory = $response->original['data']->resource;

        $this->putDirectory($directory->id)
            ->assertStatus(200);

        $this->deleteDirectory($directory->id)
            ->assertStatus(200);

        // Inside Organization 3
        $response = $this->postDirectory(3, 30)
            ->assertStatus(200);

        /** @var Directory $directory */
        $directory = $response->original['data']->resource;

        $this->putDirectory($directory->id, 3, 30)
            ->assertStatus(200);

        $this->deleteDirectory($directory->id)
            ->assertStatus(200);
    }

    public function testApiDirectoryFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3, 3));
        // Inside Organization 3
        $response = $this->postDirectory(3, 32)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Directory')
                    ->where('data.organization_id', 3)
                    ->etc()
            );

        /** @var Directory $directory */
        $directory = $response->original['data']->resource;

        $this->putDirectory($directory->id, 3, 32)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Directory Updated')
                    ->where('data.organization_id', 3)
                    ->etc()
            );

        $this->deleteDirectory($directory->id)
            ->assertStatus(200);

        // Inside Organization 1
        $response = $this->postDirectory()
            ->assertStatus(200);

        /** @var Directory $directory */
        $directory = $response->original['data']->resource;

        $this->putDirectory($directory->id)
            ->assertStatus(200);

        $this->deleteDirectory($directory->id)
            ->assertStatus(200);

        // Inside Organization 2
        $response = $this->postDirectory(2, 20)
            ->assertStatus(200);

        /** @var Directory $directory */
        $directory = $response->original['data']->resource;

        $this->putDirectory($directory->id, 2, 20)
            ->assertStatus(200);

        $this->deleteDirectory($directory->id)
            ->assertStatus(200);
    }

    //
    // Group
    //
    public function testApiGroupDirectoriesListing()
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

    public function testApiGroupDirectoriesAccess()
    {
        $this->assertTrue($this->_login());

        $this->get($this->appUrl . '/api/group/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/group/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/group/3', $this->headers)
            ->assertStatus(200);
    }

    public function testApiGroupCrud()
    {
        $this->assertTrue($this->_login());

        $response = $this->postGroup()
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Group')
                    ->etc()
            );

        /** @var Group $group */
        $group = $response->original['data']->resource;

        $this->putGroup($group->id)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Group Updated')
                    ->etc()
            );

        $this->deleteGroup($group->id)
            ->assertStatus(200);
    }

    //
    // Report
    //
    public function testApiReportFromOrganization1ReportsListing()
    {
        $this->assertTrue($this->_login());
        $this->get($this->appUrl . '/api/report', $this->headers)
            ->assertStatus(200)
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
            // reports from organization 2 - should not be here at al
            ->assertJsonMissing(['name' => 'Report 20'])
            ->assertJsonMissing(['name' => 'Report 21']);
    }

    public function testApiReportFromOrganization2ReportsListing()
    {
        $this->assertTrue($this->_login(2, 2, 2));
    }

    public function testApiReportFromOrganization3ReportsListing()
    {
        $this->assertTrue($this->_login(3, 3, 3));
    }

    public function testApiReportFromOrganization1ReportsAccess()
    {
        $this->assertTrue($this->_login());
        // Inside Organization 1
        $this->get($this->appUrl . '/api/report/1', $this->headers)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has('category')
                    ->has('conf_connector')
                    ->has('directory')
                    ->has('organization')
                    ->has('parameters')
                    ->count('parameters', 3)
                    ->has('parameters.2.parameter_input.values')
                    ->count('parameters.2.parameter_input.values', 33)
                    ->has('user')
                    ->etc()
            );
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

        // Inside Organization 2
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

    public function testApiReportFromOrganization2ReportsAccess()
    {
        $this->assertTrue($this->_login(2, 2));
        // Inside Organization 1
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

        // Inside Organization 2
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

    public function testApiReportFromOrganization3ReportsAccess()
    {
        $this->assertTrue($this->_login(3, 3));
        // Inside Organization 1
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

        // Inside Organization 2
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
        // Reports inside Organization 1
        $response = $this->postReport()
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.user_id', 1)
                    ->has('data.id')
                    ->has('data.allowed_groups')
                    ->count('data.allowed_groups', 2)
                    ->has('data.allowed_users')
                    ->count('data.allowed_users', 3)
                    ->etc()
            );

        /** @var Report $report */
        $report = $response->original['data']->resource;

        $this->putReport($report->id)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Report ' . $report->id . ' - modified')
                    ->has('data.allowed_groups')
                    ->count('data.allowed_groups', 1)
                    ->has('data.allowed_users')
                    ->count('data.allowed_users', 2)
                    ->etc()
            );

        $this->deleteReport($report->id)
            ->assertStatus(200);

        $this->putReportUpdateQueries(9, 1, 'glopi', 'glopa')
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.query_init', 'glopi')
                    ->where('data.query_cleanup', 'glopa')
                    ->etc()
            );

        $this->putReportUpdateQueries(9)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.query_init', null)
                    ->where('data.query_cleanup', null)
                    ->etc()
            );


        // Reports inside Organization 2
        $response = $this->postReport(2)
            ->assertStatus(200);

        $report = $response->original['data']->resource;

        $this->putReport($report->id, 2)
            ->assertStatus(200);

        $this->putReportUpdateQueries($report->id, 2, 'glopi', 'glopa')
            ->assertStatus(200);

        $this->deleteReport($report->id)
            ->assertStatus(200);

        // Reports inside Organization 3
        $response = $this->postReport(3)
            ->assertStatus(200);

        $report = $response->original['data']->resource;

        $this->putReport($report->id, 3)
            ->assertStatus(200);

        $this->putReportUpdateQueries($report->id, 3, 'glopi', 'glopa')
            ->assertStatus(200);

        $this->deleteReport($report->id)
            ->assertStatus(200);

    }

    public function testApiReportFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2));
        $response = $this->postReport(2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.user_id', 1)
                    ->has('data.id')
                    ->has('data.allowed_groups')
                    ->count('data.allowed_groups', 2)
                    ->has('data.allowed_users')
                    ->count('data.allowed_users', 3)
                    ->etc()
            );

        /** @var Report $report */
        $report = $response->original['data']->resource;

        $this->putReport($report->id, 2)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Report ' . $report->id . ' - modified')
                    ->has('data.allowed_groups')
                    ->count('data.allowed_groups', 1)
                    ->has('data.allowed_users')
                    ->count('data.allowed_users', 2)
                    ->etc()
            );

        $this->deleteReport($report->id)
            ->assertStatus(200);

        // Reports inside Organization 1
        $response = $this->postReport()
            ->assertStatus(200);

        $report = $response->original['data']->resource;

        $this->putReport($report->id)
            ->assertStatus(200);

        $this->putReportUpdateQueries($report->id, 1, 'glopi', 'glopa')
            ->assertStatus(200);

        $this->deleteReport($report->id)
            ->assertStatus(200);

        // Reports inside Organization 3
        $response = $this->postReport(3)
            ->assertStatus(200);

        $report = $response->original['data']->resource;

        $this->putReport($report->id, 3)
            ->assertStatus(200);

        $this->putReportUpdateQueries($report->id, 3, 'glopi', 'glopa')
            ->assertStatus(200);

        $this->deleteReport($report->id)
            ->assertStatus(200);
    }

    public function testApiReportFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));
        $response = $this->postReport(3)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.user_id', 1)
                    ->has('data.id')
                    ->has('data.allowed_groups')
                    ->count('data.allowed_groups', 2)
                    ->has('data.allowed_users')
                    ->count('data.allowed_users', 3)
                    ->etc()
            );

        /** @var Report $report */
        $report = $response->original['data']->resource;

        $this->putReport($report->id, 3)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Test Report ' . $report->id . ' - modified')
                    ->has('data.allowed_groups')
                    ->count('data.allowed_groups', 1)
                    ->has('data.allowed_users')
                    ->count('data.allowed_users', 2)
                    ->etc()
            );

        $this->deleteReport($report->id)
            ->assertStatus(200);

        // Reports inside Organization 1
        $response = $this->postReport()
            ->assertStatus(200);

        $report = $response->original['data']->resource;

        $this->putReport($report->id)
            ->assertStatus(200);

        $this->putReportUpdateQueries($report->id, 1, 'glopi', 'glopa')
            ->assertStatus(200);

        $this->deleteReport($report->id)
            ->assertStatus(200);

        // Reports inside Organization 2
        $response = $this->postReport(2)
            ->assertStatus(200);

        $report = $response->original['data']->resource;

        $this->putReport($report->id, 2)
            ->assertStatus(200);

        $this->putReportUpdateQueries($report->id, 2, 'glopi', 'glopa')
            ->assertStatus(200);

        $this->deleteReport($report->id)
            ->assertStatus(200);
    }

    //
    // Report Data View
    //
    public function testApiReportDataViewFromOrganization1DataViewsListing()
    {
        $this->assertTrue($this->_login());
        $this->get($this->appUrl . '/api/report-data-view/', $this->headers)
            ->assertStatus(200);
    }

    public function testApiReportDataViewFromOrganization2DataViewsListing()
    {
        $this->assertTrue($this->_login(2, 2));
        $this->get($this->appUrl . '/api/report-data-view/', $this->headers)
            ->assertStatus(200);
    }

    public function testApiReportDataViewFromOrganization3DataViewsListing()
    {
        $this->assertTrue($this->_login(3, 3));
        $this->get($this->appUrl . '/api/report-data-view/', $this->headers)
            ->assertStatus(200);
    }

    public function testApiReportDataViewFromOrganization1DataViewsAccess()
    {
        $this->assertTrue($this->_login());
        // Inside Organization 1
        $this->get($this->appUrl . '/api/report-data-view/1', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report-data-view/1/run', $this->headers)
            ->assertStatus(200);

        // Parent report not visible for VIEWERs.
        $this->get($this->appUrl . '/api/report-data-view/5', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report-data-view/5/run', $this->headers)
            ->assertStatus(200);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/report-data-view/4', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report-data-view/4/run', $this->headers)
            ->assertStatus(200);
    }

    public function testApiReportDataViewFromOrganization2DataViewsAccess()
    {
        $this->assertTrue($this->_login(2, 2));
        // Inside Organization 1
        $this->get($this->appUrl . '/api/report-data-view/1', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report-data-view/1/run', $this->headers)
            ->assertStatus(200);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/report-data-view/4', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report-data-view/4/run', $this->headers)
            ->assertStatus(200);
    }

    public function testApiReportDataViewFromOrganization3DataViewsAccess()
    {
        $this->assertTrue($this->_login(3, 3));
        // Inside Organization 1
        $this->get($this->appUrl . '/api/report-data-view/1', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report-data-view/1/run', $this->headers)
            ->assertStatus(200);

        // Inside Organization 2
        $this->get($this->appUrl . '/api/report-data-view/4', $this->headers)
            ->assertStatus(200);
        $this->post($this->appUrl . '/api/report-data-view/4/run', $this->headers)
            ->assertStatus(200);
    }

    public function testApiReportDataViewFromOrganization1Crud()
    {
        $this->assertTrue($this->_login());
        // Inside Organization 1
        $response = $this->postReportDataView()
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.type', 2)
                    ->etc()
            );

        /** @var ReportDataView $reportDataView */
        $reportDataView = $response->original['data']->resource;

        $this->putReportDataView($reportDataView->id, 1)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.title', 'Data view for report 1 - modified')
                    ->etc()
            );

        $this->deleteReportDataView($reportDataView->id)
            ->assertStatus(200);

        $this->putReportDataViewUpdateQuery(1, 1, 'glopi - modified')
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.query', 'glopi - modified')
                    ->etc()
            );

        $this->putReportDataViewUpdateQuery(1, 1, '')
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.query', null)
                    ->etc()
            );

        // Inside Organization 2
        $response = $this->postReportDataView(19)
            ->assertStatus(200);

        $reportDataView = $response->original['data']->resource;

        $this->putReportDataView($reportDataView->id, 19)
            ->assertStatus(200);

        $this->putReportDataViewUpdateQuery($reportDataView->id, 19, 'glopi')
            ->assertStatus(200);

        $this->deleteReportDataView($reportDataView->id)
            ->assertStatus(200);
    }

    public function testApiReportDataViewFromOrganization2Crud()
    {
        $this->assertTrue($this->_login(2, 2));
        // Inside Organization 1
        $response = $this->postReportDataView(2)
            ->assertStatus(200);

        $reportDataView = $response->original['data']->resource;

        $this->putReportDataView($reportDataView->id, 2)
            ->assertStatus(200);

        $this->putReportDataViewUpdateQuery($reportDataView->id, 2, 'glopi')
            ->assertStatus(200);

        $this->deleteReportDataView($reportDataView->id)
            ->assertStatus(200);

        // Inside Organization 2
        $response = $this->postReportDataView(19)
            ->assertStatus(200);

        $reportDataView = $response->original['data']->resource;

        $this->putReportDataView($reportDataView->id, 19)
            ->assertStatus(200);

        $this->putReportDataViewUpdateQuery($reportDataView->id, 19, 'glopi')
            ->assertStatus(200);

        $this->deleteReportDataView($reportDataView->id)
            ->assertStatus(200);
    }

    public function testApiReportDataViewFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));
        // Inside Organization 1
        $response = $this->postReportDataView(2)
            ->assertStatus(200);

        $reportDataView = $response->original['data']->resource;

        $this->putReportDataView($reportDataView->id, 2)
            ->assertStatus(200);

        $this->putReportDataViewUpdateQuery($reportDataView->id, 2, 'glopi')
            ->assertStatus(200);

        $this->deleteReportDataView($reportDataView->id)
            ->assertStatus(200);

        // Inside Organization 2
        $response = $this->postReportDataView(19)
            ->assertStatus(200);

        $reportDataView = $response->original['data']->resource;

        $this->putReportDataView($reportDataView->id, 19)
            ->assertStatus(200);

        $this->putReportDataViewUpdateQuery($reportDataView->id, 19, 'glopi')
            ->assertStatus(200);

        $this->deleteReportDataView($reportDataView->id)
            ->assertStatus(200);
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
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 5)
                    ->etc()
            );
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
            ->assertStatus(200);

        // Report Parameter Input from Organization 3
        $this->get($this->appUrl . '/api/report-parameter-input/12', $this->headers)
            ->assertStatus(200);
    }

    public function testApiReportParameterInputFromOrganization2CategoriesShow()
    {
        $this->assertTrue($this->_login(2, 2));

        // Report Parameter Input from Organization 1
        $this->get($this->appUrl . '/api/report-parameter-input/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/report-parameter-input/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/report-parameter-input/3', $this->headers)
            ->assertStatus(200);

        // Report Parameter Input from Organization 2
        $this->get($this->appUrl . '/api/report-parameter-input/8', $this->headers)
            ->assertStatus(200);

        // Report Parameter Input from Organization 3
        $this->get($this->appUrl . '/api/report-parameter-input/12', $this->headers)
            ->assertStatus(200);
    }

    public function testApiReportParameterInputFromOrganization3CategoriesShow()
    {
        $this->assertTrue($this->_login(3, 3));

        // Report Parameter Input from Organization 1
        $this->get($this->appUrl . '/api/report-parameter-input/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/report-parameter-input/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/report-parameter-input/3', $this->headers)
            ->assertStatus(200);

        // Report Parameter Input from Organization 2
        $this->get($this->appUrl . '/api/report-parameter-input/8', $this->headers)
            ->assertStatus(200);

        // Report Parameter Input from Organization 3
        $this->get($this->appUrl . '/api/report-parameter-input/12', $this->headers)
            ->assertStatus(200);
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
            ->assertStatus(200);

        $this->putReportParameterInput(7, 2)
            ->assertStatus(200);

        $this->deleteReportParameterInput(7)
            ->assertStatus(200);

        // Report Parameter Input from Organization 3
        $this->postReportParameterInput(3)
            ->assertStatus(200);

        $this->putReportParameterInput(12, 3)
            ->assertStatus(200);

        $this->deleteReportParameterInput(12)
            ->assertStatus(200);

        (new CreateReportParameterInputsTable)->down();
        (new CreateReportParameterInputsTable)->up();
        (new ReportParameterInputsSeeder)->run();
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
            ->assertStatus(200);

        $this->putReportParameterInput(1, 1)
            ->assertStatus(200);

        $this->deleteReportParameterInput(1)
            ->assertStatus(200);

        // Report Parameter Input from Organization 3
        $this->postReportParameterInput(3)
            ->assertStatus(200);

        $this->putReportParameterInput(12, 3)
            ->assertStatus(200);

        $this->deleteReportParameterInput(12)
            ->assertStatus(200);
    }

    public function testApiReportParameterInputFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));

        // Report Parameter Input from Organization 3
        $response = $this->postReportParameterInput(3)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Country')
                    ->where('data.conf_connector_id', 3)
                    ->where('data.default_value', '74')
                    ->etc()
            );

        /** @var ReportParameterInput $reportParameterInput */
        $reportParameterInput = $response->original['data']->resource;

        $this->putReportParameterInput($reportParameterInput->id, 3)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data.name', 'Country modified')
                    ->where('data.conf_connector_id', 3)
                    ->where('data.default_value', '13')
                    ->etc()
            );

        $this->deleteReportParameterInput($reportParameterInput->id)
            ->assertStatus(200);

        // Report Parameter Input from Organization 1
        $this->postReportParameterInput(1)
            ->assertStatus(200);

        $this->putReportParameterInput(1, 1)
            ->assertStatus(200);

        $this->deleteReportParameterInput(1)
            ->assertStatus(200);

        // Report Parameter Input from Organization 2
        $this->postReportParameterInput(2)
            ->assertStatus(200);

        $this->putReportParameterInput(7, 2)
            ->assertStatus(200);

        $this->deleteReportParameterInput(7)
            ->assertStatus(200);
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
                    ->count('data', 6)
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
                    ->count('data', 4)
                    ->etc()
            );
    }

    public function testApiUserFromOrganization1UsersShow()
    {
        $this->assertTrue($this->_login());

        $this->get($this->appUrl . '/api/user/1', $this->headers)
            ->assertStatus(200);

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
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/7', $this->headers)
            ->assertStatus(200);
    }

    public function testApiUserFromOrganization2UsersShow()
    {
        $this->assertTrue($this->_login(2, 2));

        $this->get($this->appUrl . '/api/user/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/3', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/4', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/5', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/6', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/9', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/7', $this->headers)
            ->assertStatus(200);
    }

    public function testApiUserFromOrganization3UsersShow()
    {
        $this->assertTrue($this->_login(3, 3));

        $this->get($this->appUrl . '/api/user/1', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/2', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/3', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/4', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/5', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/6', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/9', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/7', $this->headers)
            ->assertStatus(200);

        $this->get($this->appUrl . '/api/user/10', $this->headers)
            ->assertStatus(200);
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
        $random_name = Str::random(5);
        $this->postUser($random_name, 2)
            ->assertStatus(200);

        $random_name = Str::random(5);
        $this->putUser($random_name, 9, 2)
            ->assertStatus(200);

        // User from Organization 3
        $random_name = Str::random(5);
        $this->postUser($random_name, 3)
            ->assertStatus(200);

        $random_name = Str::random(5);
        $this->putUser($random_name, 10, 3)
            ->assertStatus(200);

        // Super admin - do not touch
        $random_name = Str::random(5);
        $this->putUser($random_name, 1,)
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
        $random_name = Str::random(5);
        $this->postUser($random_name)
            ->assertStatus(200);

        $random_name = Str::random(5);
        $this->putUser($random_name, 3)
            ->assertStatus(200);

        // User from Organization 3
        $random_name = Str::random(5);
        $this->postUser($random_name, 3)
            ->assertStatus(200);

        $random_name = Str::random(5);
        $this->putUser($random_name, 10, 3)
            ->assertStatus(200);

        // Super admin - do not touch
        $random_name = Str::random(5);
        $this->putUser($random_name, 1,)
            ->assertStatus(401);

        $this->deleteUser(1)
            ->assertStatus(401);
    }

    public function testApiUserFromOrganization3Crud()
    {
        $this->assertTrue($this->_login(3, 3));

        // User from Organization 3
        $random_name = Str::random(5);

        $response = $this->postUser($random_name, 3)
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

        $this->putUser($random_name, $user->id, 3)
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
        $random_name = Str::random(5);
        $this->postUser($random_name)
            ->assertStatus(200);

        $random_name = Str::random(5);
        $this->putUser($random_name, 3)
            ->assertStatus(200);

        // User from Organization 2
        $random_name = Str::random(5);
        $this->postUser($random_name, 2)
            ->assertStatus(200);

        $random_name = Str::random(5);
        $this->putUser($random_name, 9, 2)
            ->assertStatus(200);

        // Super admin - do not touch
        $random_name = Str::random(5);
        $this->putUser($random_name, 1,)
            ->assertStatus(401);

        $this->deleteUser(1)
            ->assertStatus(401);
    }
}
