**ERD**

![Alt text](ERD.png)

**Implemented features**
- System admins can view teams
- System admins may upload/download system-wide parts
- Team admins can view system-wide parts
- Team admins can associate parts to their team
- Team admins can upload/download team pricing details
- Team members can view and search team parts and part information

**Additional features**
- Session's team is changable for members/team admins with multiple teams
- Validation results for system-wide part uploads
- Validation results for team pricing uploads

**Necessary improvements**
- Server-side pagination for datatables
- Batch team part association in a job

**Plan for improvements**
- Implement server-side pagination and filtering for all datatables
- Usage of [Laravel JSON:API](https://laraveljsonapi.io/) that adheres to [json:api](https://jsonapi.org/) specifications: this should speed up the process of api creation and bring to focus on configuration and designing the custom filters
- Usage of [pinia-jsonapi](https://github.com/mrichar1/pinia-jsonapi) on client-side to consume the standardized api endpoints