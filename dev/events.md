| Resource                   | C   | U   | D   | WS - Organization | WS - Private | Front-end | Event name                            | Notes                                                                |
|----------------------------|-----|-----|-----|-------------------|--------------|-----------|---------------------------------------|----------------------------------------------------------------------|
| Category                   | ✓   | ✓   | ✓   | ✓                 | ✖            | ✓         | APICacheCategoriesUpdated             | Event's response contains the full list of categories                |
| ConfConnector              | ∼   | ∼   | ∼   | ∼                 | ∼            | ∼         |                                       |                                                                      |
| Directory                  | ✓   | ✓   | ✓   | ✓                 | ✖            | ✓         | APICacheDirectoriesUpdated            | Event's response contains the full list of directories               |
| Group                      | ∼   | ∼   | ∼   | ∼                 | ∼            | ∼         |                                       |                                                                      |
| Organization               | ∼   | ∼   | ∼   | ∼                 | ∼            | ∼         |                                       |                                                                      |
| Report                     | ✓   | ✓   | ✓   | ✓                 | ✖            | ✓         | APICacheReportCreated/Updated/Deleted | Event's response contains only one Report.                           |                                                       |
| ReportDataView             | ✖   | ✖   | ✖   | ✖                 | ✖            | ✖         | /                                     | Report's DataView CRUD isn't websocketed because it's not necessary. |
| ReportDataViewJsController | ✖   | ✖   | ✖   | ✖                 | ✖            | ✖         | /                                     | Same thing as Report's DataView event.                               |
| ReportParameter            | ✖   | ✖   | ✖   | ✖                 | ✖            | ✖         | /                                     | Same thing as Report's DataView event.                               |
| ReportParameterInput       | ✓   | ✓   | ✓   | ✓                 | ✖            | ✓         | APICacheReportParameterInputsUpdated  | Event's response contains the full list of parameters's inputs.      |
| User                       | ✓   | ✓   | ✓   | ✓                 | ✖            | ✓         | APICacheUserUpdated                   | Event's response contains the full list of Users.                    |

* ✓ - done
* ✖ - not needed
* ∼ - to do

* WS - Organization - event sent on  `organization.<organization.id>`
* WS - Private - event sent on `user.<user.id>`
* Front-end - event correctly handled by the front-end, in order to refresh local storage & co.
