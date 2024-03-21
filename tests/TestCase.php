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

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Cookie;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected string $appUrl = '';
    protected string $acceptEncoding = '';
    protected string $accept = '';
    protected string $contentType = '';
    protected string $host = '';
    protected string $origin = '';
    protected string $referer = '';
    protected string $locale = 'fr'; // fr | en
    /** @var string[] */
    protected array $headers = [];
    /** @var Cookie[] */
    protected array $cookies = [];

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->appUrl = 'http://api.rootdb.localhost.com';
        $this->acceptEncoding = 'gzip, deflate, br';
        $this->accept = '*/*';
        $this->contentType = 'multipart/form-data';
        $this->host = 'api.rootdb.localhost.com';
        $this->origin = 'http://frontend-react.rootdb.localhost.com';
        $this->referer = 'http://frontend-react.rootdb.localhost.com';

        $this->headers = [
            'Accept-Encoding' => $this->acceptEncoding,
            'Accept'          => $this->accept,
            'Content-Type'    => $this->contentType,
            'Host'            => $this->host,
            'Origin'          => $this->origin,
            'Referer'         => $this->referer,
        ];
    }

    //
    // Auth
    //
    protected function postLogin(string $username, int $organizationId = 0): TestResponse
    {
        $fields = ['name' => $username, 'password' => 'a'];
        if ($organizationId > 0) {

            $fields = array_merge($fields, ['organization-id' => $organizationId]);
        }

        $response = $this->post($this->appUrl . '/api/login/', $fields, $this->headers);
        array_push($this->headers, ['x-xsrf-token' => $response->headers->getCookies()[0]->getValue()]);

        return $response;
    }

    public function postLogout(): TestResponse
    {
        return $this->post($this->appUrl . '/api/logout');
    }

    //
    // Category
    //
    protected function postCategory(int $organizationId = 1): TestResponse
    {
        return $this->post($this->appUrl . '/api/category/', [
            'name'            => 'Test Category',
            'description'     => 'Description test Category.',
            'organization_id' => $organizationId,
            'color_hex'       => '112233',
        ],                 $this->headers);
    }

    protected function putCategory(int $categoryId, int $organizationId = 1): TestResponse
    {
        return $this->put($this->appUrl . '/api/category/' . $categoryId, [
            'name'            => 'Test Category Updated',
            'description'     => 'Description test Category updated.',
            'organization_id' => $organizationId,
            'color_hex'       => '112233',
        ],                $this->headers);
    }

    protected function deleteCategory(int $categoryId): TestResponse
    {
        return $this->delete($this->appUrl . '/api/category/' . $categoryId, $this->headers);
    }

    //
    // ConfConnector
    //
    protected function postConfConnector(int $organizationId = 1): TestResponse
    {
        return $this->post($this->appUrl . '/api/conf-connector/', [
            'name'                  => 'Local connexion',
            'connector_database_id' => 1,
            'organization_id'       => $organizationId,
            'host'                  => 'db-api',
            'port'                  => 33036,
            'database'              => 'up-api',
            'username'              => 'up_api_dev',
            'password'              => 'yaesheekoh5efeen2Que0gu2uupich',
            'timeout'               => 15,
        ],                 $this->headers);
    }

    protected function putConfConnector(int $confConnectorId, int $organizationId = 1): TestResponse
    {
        return $this->put($this->appUrl . '/api/conf-connector/' . $confConnectorId, [
            'name'                  => 'Local connexion updated',
            'connector_database_id' => 1,
            'organization_id'       => $organizationId,
            'host'                  => 'db-api',
            'port'                  => 33036,
            'database'              => 'up-api',
            'username'              => 'up_api_dev',
            'password'              => 'yaesheekoh5efeen2Que0gu2uupich',
            'use_ssl'               => 1,
            'timeout'               => 15,
            'ssl_ca'                => '-----BEGIN CERTIFICATE----- MIIFwzCCA6ugAwIBAgIUR6dHuoo4du4qcOfaTRMsylnhmlswDQYJKoZIhvcNAQEL BQAwcDELMAkGA1UEBhMCRlIxEzARBgNVBAgMClNvbWUtU3RhdGUxHjAcBgNVBAcM FUNoYXNzZW5ldWlsIGR1IFBvaXRvdTEUMBIGA1UECgwLSWNhbm9ww4PCqWUxFjAU BgNVBAMMDU1hcmlhREIgYWRtaW4wIBcNMjEwMzAyMTAzNzAwWhgPMzAyMDA3MDMx MDM3MDBaMHAxCzAJBgNVBAYTAkZSMRMwEQYDVQQIDApTb21lLVN0YXRlMR4wHAYD VQQHDBVDaGFzc2VuZXVpbCBkdSBQb2l0b3UxFDASBgNVBAoMC0ljYW5vcMODwqll MRYwFAYDVQQDDA1NYXJpYURCIGFkbWluMIICIjANBgkqhkiG9w0BAQEFAAOCAg8A MIICCgKCAgEAoHtgdlAnq5VJahMmxTlUUob2zGC9eMQEDwRbWGmNGH3+DBXlSVtl 2W9RI95jbyU4rXYJrG+HpFsZ7EXw2ZEj1bp9HqLqtWh9LU1lZ1GwT5UArg0zh9rM pXq4mYB4V9Snmz4BR8w2diVufKaYL5NHmwUHao0RWUGbWxw\/xiNtAEttZ7+zanVj T6sO9tTkOBakohg3fKoiPex0s2pOTMn9syeeFYlsRCRDrkSxrNX\/TFRkrE32IU9e \/RKA9JYQhdcCSRlC6+1\/HSha1NrUG\/3HBXFZf8WIC3BC8H2uNyG8ajjoeD415qwH hs2aVv3mMIIYJu8G5+e25e8GGFNmQwOhY9RYnUyD4zb6dr\/f03MDNNuudKtBMB4W 4NYavJHwoXZaDzNU7AgO\/w+WNDrq+KcX+CLHuwQvvexK8K4U\/Y3U01ic8oBnWOiA C3I\/tmRJAMrRkwdcoPv2j5f2sfVJkFMf71Y3REQPJQVq2eAQZcyBu1wZR++h2gnh GJ8BsAprSlJPysYT7jkZ7u1iglw1UbJbA3azX5WvnOEjGkol0MwT9XC2E9bbltem PlqNIIdvR7GXyfmBRJ6abt777GjRW6bD1wFljfFn6D6inYv1nOWsFQIDi13Cz2wQ k9pk\/X0xZPMb4CIjbhsv9u7Fz1ZzTneY\/u9tmS+cZQjJG4Dh\/Q2Q8tcCAwEAAaNT MFEwHQYDVR0OBBYEFP6F0HTyeRNQmCPD40p3JLTXBMwWMB8GA1UdIwQYMBaAFP6F 0HTyeRNQmCPD40p3JLTXBMwWMA8GA1UdEwEB\/wQFMAMBAf8wDQYJKoZIhvcNAQEL BQADggIBAD0d6\/Z+iwDsmF2nQ8yDNZNdOxyy2YPwRf1lahCyn9LmCvo6XzsD05S1 KL+63G2idNTvMWgzGk2iuznrP2s6EVUuccI06lPipqzaZ4jlMpZZ3TUygOgl8umj q3gv5spHSr2ZHV5qC6wdyxZjZpHhtP8ReEjtGfk5H6JbSS0qkjGeBAAyhxI9aCS3 IC1guN6TcXLZ3\/6vXX5QyXU0dUjgkO9eHNOjq8tJhb3U1yq04bFynY8MMP6\/VBRS gGExCoAzFY6Y8atLUiw2TsCRKikf4iOkq9aH\/SNHY5QlvfKZJojvmRpNYttAc4aK +lafZ18YNKTZWK0EkJeaZlSNopgmhan9jKp1vuv20dicic7eKkxJpYHE7cESs1nT h3Ji2RcQsxp3u0WGVrM4h8JVLKYbdYziN0\/a6RJMx1VbclqN4AHehlL84AFjERh\/ wxgS84U1CkgwBBeu8uNJwIfcuWeBqmlGWmBzNdqZve8wGKArU3EtS2RY9il1l5wr F2xGMepob59lZt5NgDYOpjW+mQwWE4n8qCbq2ZEhhYpCl88a+ipg2uoLG\/+irnej 22ZS4\/xSy+129yZPWA2qa+6cwhl75gO25XsBrTbKMGo7ztnfaQyAri\/ZJjt9rLfE +uBKIeDsWscn9aro1bahE493ddNRCDSap4qV5rGeaGzS+u9XLar5 -----END CERTIFICATE-----',
            'ssl_key'               => '-----BEGIN RSA PRIVATE KEY----- MIIEowIBAAKCAQEA0PSwt84GytnRer4xBMe636HmNMwm8TpLaXlrfo5ta2JF+t+U sW75gVQni0IbRZYtZY+Kebt5505zO6pbW324\/pgvZ0bNPiorDJ76SZDnYNuT31px 1wAWD+uMo9KVAz+LdEriPUSzUYvuiqpuJuZv95BUPNM\/jCJtlA2Yk4IUBp4npPCq 6ui4VuEaaN5a4UDbjw\/76zT5aAaABAcWLIJXXmsevxjeR3HmnHMKmFs00IkshA91 VztmTnBWkASuGnN50tYOSFui9CQEQ7FffzJrnprn6ZLQHgImcOkXwZ\/C4O7jxihn ZczI1RqNlAKcCD70NiO3tPE7m73tUMc+n6UlWQIDAQABAoIBAAzWG00PUQeBHgdG S8iZZHd8gKHZsMK87AkMtnfN1Bb2sIna1k2YHae+PbemVfqOYTeN+9nClJiLDzUp H\/ec35J0UuUrSkx5Vq+tzH5ccnpWwtzDt56XmMNdmwQtWY4bhzubpg5RfBqUWBEY qy+klFhG+4XJDGxVaRnhQ\/A723MUg7kKh1u2cbJo4Unie1CYi4qlGm2X75Jy09Om 0I0K6+T6FfYKAc0c9oV8o8N5vvuU5at0xiGg\/Ra\/5aYLfm\/aqbsekxhsIdaimoLJ GPKMnYfFxKL8I8HGZkq8eYs8Q9voYSOq2df35jo8M2++D+7SFv\/wa1DyjtUCvKsV 3pocmakCgYEA7lYaWkrqUxLxhrJS3X6ACDjOKCR\/sQm2r\/80yQy2eAN+OhDUgrwZ 3wAHVd6lF5pPmzezEIZvOUv3IYWpKH29OyXhLp2CdwBaY8hGiwfnjCEpYWqtrGcg GAwgiKfcsxjxL\/CXUs9bwncDK\/g8zMVLP3c+mEr76WToX2kUJWw5rjMCgYEA4HEo cYwq0Q3ZuO0d6tBCJvD8cxSD3YSxVkiQaZ6\/uz6RJEJtEC+6\/m0J1ILuS6w\/2L9M JFAncM9JfeocKLCbsSU+PTQeFURCn4DpIU0ZRTQ8d9KnKK34j8ixZUdt6b7RwtZ1 IiSjPvN+KILg6w5tjK8plt62IUemAwsFBJmXOkMCgYEAzV60ZomXUO5J4N9YODQA 7xTD0CNjRJIyMYWfXn7t3IxmAHLwK5caU+YabAvmBmiZoA5m5h5xSNYEpYYfNRzk KkuBtkFTYmeTe9ffsX2mMEGC\/saF0MEsDoyknBzJOCqN6dlPC7RSRUd4HDNTcL+x D4cZEPHMEFk7Qruw+G5BZbECgYBycEA25UlPnshMylpeyCFyyZ1u8B7sbCQf4o\/\/ yrnoN1a6LkR95FhsMhy5BqmKXCGR2rhwK45wrsDCOwRwmtxHzr2VZ2WPYma1\/Xzh RfaEmsXaMsaYr1v1tFb\/VRRuAqXhuoevCQ9TocPJ1DHqqEijWwzRqG0lOusi0hOU 7Nt3EQKBgBs1GoIalPdMhqUz9gaiVPXHWrMBvfpIqGZdb0R1xPR0EThCB1jyZv2j DOP9uuCh0hLheYRn2Li2thJAzl10tDpKqQTcflpPi4gTIX2UWytCpJ\/pSB1YxI8r 7q+zafl8LJ3220bsSHgcywwXT1Ms1gUPB9h7OZzPeYhmvJv7yHnp -----END RSA PRIVATE KEY-----',
            'ssl_cert'              => '-----BEGIN CERTIFICATE----- MIIEPjCCAiYCAQEwDQYJKoZIhvcNAQELBQAwcDELMAkGA1UEBhMCRlIxEzARBgNV BAgMClNvbWUtU3RhdGUxHjAcBgNVBAcMFUNoYXNzZW5ldWlsIGR1IFBvaXRvdTEU MBIGA1UECgwLSWNhbm9ww4PCqWUxFjAUBgNVBAMMDU1hcmlhREIgYWRtaW4wIBcN MjEwNTE4MTQ1NjA5WhgPMzAyMDA5MTgxNDU2MDlaMFgxCzAJBgNVBAYTAkZSMRMw EQYDVQQIDApTb21lLVN0YXRlMREwDwYDVQQKDAhJY2Fub3BlZTEhMB8GA1UEAwwY c2VydmljZV8xX21hcmlhZGJfY2xpZW50MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A MIIBCgKCAQEA0PSwt84GytnRer4xBMe636HmNMwm8TpLaXlrfo5ta2JF+t+UsW75 gVQni0IbRZYtZY+Kebt5505zO6pbW324\/pgvZ0bNPiorDJ76SZDnYNuT31px1wAW D+uMo9KVAz+LdEriPUSzUYvuiqpuJuZv95BUPNM\/jCJtlA2Yk4IUBp4npPCq6ui4 VuEaaN5a4UDbjw\/76zT5aAaABAcWLIJXXmsevxjeR3HmnHMKmFs00IkshA91Vztm TnBWkASuGnN50tYOSFui9CQEQ7FffzJrnprn6ZLQHgImcOkXwZ\/C4O7jxihnZczI 1RqNlAKcCD70NiO3tPE7m73tUMc+n6UlWQIDAQABMA0GCSqGSIb3DQEBCwUAA4IC AQA14Jb3szC\/fH3BEaaJLCxdAVzt4bDV0Gwab7FlWxZTK9omITsJe3MtnXGP1RNQ h2wD3sptNvW6Z6f1j47369fMi0B08jHGlEqYD9VsSUFxMGUuXKcINj5\/NQKBEcN1 ftOl3c+lg\/4p8UJo4uPq2BBRv\/j00+E4BKawDMcLCKYj5ebYHmlInk+U43qH2AXi cPIfRvAhXoaoXzS7M2Ozsf7w2z7LSLpV1mmDucuQSKEOGYpb6j7XLnuFIbYZ8EV5 V\/aB6FYFzEM7PzC7mPjFLMuJ4FH0j63MUYejUQJ5HSb\/eOhLqPaZ8V0BGSuzYNNf cxSkbesU57qviUDayDoQgkTT+iDyImjcBSbgPE7nBbG41YEngxMhY2vJxp\/v7CEX RNvAT\/0khiEsRxOMKmE8dhgGP7CaRIp2PuV2zlzs+TpTJ1MUOleYOnAX7PrlAhjF jQ431fUdyPLQhaFGJZvMbXeqzCknFzpMRIWLK9GDuCOCv8meiAj6n9pjZTDCylLg VYieyCMkRb8ADFSF72bpcCZd84P2naPSlbwoQ5UPeiiWGvZ9hZNIGlKGLylc+lvN Y27vJYc3C5dmyTu3wxf6QwuHmr3SkpHrzvEKMn2TWPEmD5g\/De0Rr3jCW8kY1x2Y JUsRuLf3CQ6WMNhsVFjdQs9LNGqAmTzQa7yTy\/Qf8MSEPA== -----END CERTIFICATE-----',
        ],                $this->headers);
    }

    protected function deleteConfConnector(int $confConnectorId): TestResponse
    {
        return $this->delete($this->appUrl . '/api/conf-connector/' . $confConnectorId, $this->headers);
    }

    //
    // Directory
    //
    protected function postDirectory(int $organizationId = 1, int $parentId = 1): TestResponse
    {
        return $this->post($this->appUrl . '/api/directory/', [
            'name'            => 'Test Directory',
            'description'     => 'Description test Directory.',
            'organization_id' => $organizationId,
            'parent_id'       => $parentId,
        ],                 $this->headers);
    }

    protected function putDirectory(int $directoryId, int $organizationId = 1, int $parentId = 1): TestResponse
    {
        return $this->put($this->appUrl . '/api/directory/' . $directoryId, [
            'name'            => 'Test Directory Updated',
            'description'     => 'Description test Directory updated.',
            'organization_id' => $organizationId,
            'parent_id'       => $parentId,
        ],                $this->headers);
    }

    protected function deleteDirectory(int $directoryId): TestResponse
    {
        return $this->delete($this->appUrl . '/api/directory/' . $directoryId, $this->headers);
    }

    //
    // Group
    //
    protected function postGroup(int $organizationId = 1): TestResponse
    {
        return $this->post($this->appUrl . '/api/group/', [
            'name'            => 'Test Group',
            'organization_id' => $organizationId,
        ],                 $this->headers);
    }

    protected function putGroup(int $groupId, int $organizationId = 1): TestResponse
    {
        return $this->put($this->appUrl . '/api/group/' . $groupId, [
            'name'            => 'Test Group Updated',
            'organization_id' => $organizationId,
        ],                $this->headers);
    }

    protected function deleteGroup(int $groupId): TestResponse
    {
        return $this->delete($this->appUrl . '/api/group/' . $groupId, $this->headers);
    }

    //
    // Report
    //
    protected function postReport(
        int   $confConnector = 1,
        int   $organizationId = 1,
        int   $category_id = 3,
        int   $directory_id = 2,
        array $group_ids = [3, 1],
        array $user_ids = [3, 4]): TestResponse
    {
        return $this->post($this->appUrl . '/api/report/', [
            'conf_connector_id' => $confConnector,
            'organization_id'   => $organizationId,
            'category_id'       => $category_id,
            'directory_id'      => $directory_id,
            'name'              => 'Test Report 2',
            'description'       => 'Test Report 2 - Description',
            'query_init'        => 'SELECT * FROM `test-db`.`table_1` LIMIT 10;',
            'query_cleanup'     => '',
            'group_ids'         => $group_ids,
            'user_ids'          => $user_ids,
        ],                 $this->headers);
    }

    protected function putReport(
        int   $confConnector,
        int   $reportId,
        int   $organizationId = 1,
        int   $category_id = 3,
        int   $directory_id = 2,
        array $group_ids = [3],
        array $user_ids = [3]): TestResponse
    {
        return $this->put($this->appUrl . '/api/report/' . $reportId, [
            'conf_connector_id' => $confConnector,
            'organization_id'   => $organizationId,
            'category_id'       => $category_id,
            'directory_id'      => $directory_id,
            'name'              => 'Test Report ' . $reportId . ' - modified',
            'description'       => 'Test Report ' . $reportId . ' - Description modified',
            'query_init'        => 'SELECT * FROM `test-db`.`table_1` LIMIT 10;',
            'query_cleanup'     => '',
            'group_ids'         => $group_ids,
            'user_ids'          => $user_ids,
        ],                $this->headers);
    }

    protected function putReportUpdateQueries(int $reportId, string $queryInit = '', string $queryCleanup = ''): TestResponse
    {
        return $this->put($this->appUrl . '/api/report/' . $reportId . '/queries', [
            'query_init'        => $queryInit,
            'query_cleanup'     => $queryCleanup,
        ],                $this->headers);
    }

    protected function deleteReport(int $reportId): TestResponse
    {
        return $this->delete($this->appUrl . '/api/report/' . $reportId, $this->headers);
    }

    //
    // Report Data View
    //
    protected function postReportDataView(int $reportId = 1): TestResponse
    {
        return $this->post($this->appUrl . '/api/report-data-view/', [
            'report_id'                       => $reportId,
            'type'                            => 2,
            'title'                           => 'Data view for report ' . $reportId,
            'name'                            => 'Data view for report ' . $reportId,
            'query'                           => 'glopi',
            'position'                        => '{"colStart":1,"colEnd":1,"rowStart":1,"rowEnd":1,"dataViewId":4}',
            'report_data_view_lib_version_id' => 3
        ],                 $this->headers);
    }

    protected function putReportDataView(int $reportDataViewId, int $reportId): TestResponse
    {
        return $this->put($this->appUrl . '/api/report-data-view/' . $reportDataViewId, [
            'report_id'                       => $reportId,
            'type'                            => 2,
            'name'                            => 'Data view for report ' . $reportId,
            'title'                           => 'Data view for report ' . $reportId . ' - modified',
            'query'                           => 'glopi',
            'position'                        => '{"colStart":1,"colEnd":1,"rowStart":1,"rowEnd":1,"dataViewId":4}',
            'report_data_view_lib_version_id' => 3
        ],                $this->headers);
    }

    protected function putReportDataViewUpdateQuery(int $reportDataViewId, int $reportId, string $query): TestResponse
    {
        return $this->put($this->appUrl . '/api/report-data-view/' . $reportDataViewId . '/query', [
            'report_id'                       => $reportId,
            'type'                            => 2,
            'title'                           => 'Data view for report ' . $reportId . ' - modified',
            'query'                           => $query,
            'position'                        => '2 / 1',
            'report_data_view_lib_version_id' => 3
        ],                $this->headers);
    }

    protected function deleteReportDataView(int $reportDataViewId): TestResponse
    {
        return $this->delete($this->appUrl . '/api/report-data-view/' . $reportDataViewId, $this->headers);
    }

    //
    // Report Parameter Input
    //
    protected function postReportParameterInput(int $confConnectorId = 1): TestResponse
    {
        return $this->post($this->appUrl . '/api/report-parameter-input/', [
            'parameter_input_type_id'      => 1,
            'parameter_input_data_type_id' => 2,
            'conf_connector_id'            => $confConnectorId,
            'name'                         => 'Country',
            'query'                        => 'SELECT id, name FROM country ORDER BY name;',
            'query_default_value'          => '',
            'default_value'                => '74',
            'custom_entry'                 => 1
        ],                 $this->headers);
    }

    protected function putReportParameterInput(int $reportParameterInputId, int $confConnectorId = 1): TestResponse
    {
        return $this->put($this->appUrl . '/api/report-parameter-input/' . $reportParameterInputId, [
            'parameter_input_type_id'      => 1,
            'parameter_input_data_type_id' => 2,
            'conf_connector_id'            => $confConnectorId,
            'name'                         => 'Country modified',
            'query'                        => 'SELECT id, name FROM country ORDER BY name;',
            'query_default_value'          => '',
            'default_value'                => '13',
            'custom_entry'                 => 1
        ],                 $this->headers);
    }

    protected function deleteReportParameterInput(int $reportParameterInputId): TestResponse
    {
        return $this->delete($this->appUrl . '/api/report-parameter-input/' . $reportParameterInputId, $this->headers);
    }


    //
    // Role
    //
    protected function postRole(): TestResponse
    {
        return $this->post($this->appUrl . '/api/role/', [
            'name' => 'Test Role',
        ],                 $this->headers);
    }

    protected function putRole(int $roleId): TestResponse
    {
        return $this->put($this->appUrl . '/api/role/' . $roleId, [
            'name' => 'Test Role Updated',
        ],                $this->headers);
    }

    protected function deleteRole(int $roleId): TestResponse
    {
        return $this->delete($this->appUrl . '/api/role/' . $roleId, $this->headers);
    }

    //
    // User
    //
    protected function postUser(string $random_name, int $userOrganizationId = 1): TestResponse
    {
        return $this->post($this->appUrl . '/api/user/', [
            'name'            => $random_name,
            'email'           => $random_name . '@atomicweb.fr',
            'password'        => 'admin5',
            'is_active'       => 1,
            'organization_id' => $userOrganizationId,
            'role_ids'        => [2],
            'group_ids'       => [1, 3]
        ],                 $this->headers);
    }

    protected function putUser(string $random_name, int $userId, int $userOrganizationId = 1): TestResponse
    {
        return $this->put($this->appUrl . '/api/user/' . $userId, [
            'name'            => $random_name,
            'email'           => $random_name . '@atomicweb.fr',
            'password'        => 'admin5',
            'is_active'       => 1,
            'organization_id' => $userOrganizationId,
            'role_ids'        => [1, 2],
            'group_ids'       => [2]
        ],                $this->headers);
    }

    protected function deleteUser(int $userId): TestResponse
    {
        return $this->delete($this->appUrl . '/api/user/' . $userId, $this->headers);
    }

    protected function getChangeOrganizationUser(int $organizationId): bool
    {
        $response = $this->get($this->appUrl . '/api/user/change-organization-user?organization-id=' . $organizationId);

        return $response->status() === 200;
    }


}
