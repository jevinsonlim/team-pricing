A Laravel + Inertia Vue powered project. After cloning, setup with:
 - `composer install`
 - `npm install`
 - `cp .env.example .env`
 - `php artisan key:generate`
 - `php artisan migrate --seed`
 - `php artisan serve`
 - `npm run dev`
 - For upload jobs
    - `php artisan storage:link`
    - `php artisan queue:work`

Seed user accounts:
  - (System Admin) system_admin@example.com | password
  - (Team Admin) team_admin1@example.com | password
  - (Team Member) team_member1@example.com | password

**ERD**

![Alt text](ERD.png)

**Implemented features**
- System admins can view teams (*/teams*)
- System admins may upload/download system-wide parts (*/part_uploads* + */parts*)
- Team admins can view system-wide parts (*/parts*)
- Team admins can associate parts to their team (*/parts*)
- Team admins can upload/download team pricing details (*/team_part_uploads*)
- Team members can view and search team parts and part information (*/team_parts*)

**Additional features**
- Limited file size for uploads (1mb), for faster upload feedback and avoid memory hogging processes/jobs
- Session's team is changable for members/team admins with multiple teams
- Validation results for system-wide part uploads
- Validation results for team pricing uploads

**TODO: Improvements** 
- Server-side pagination for datatables
  - Only implemented on Parts and Team Parts datatable
- Batch team part association in a job (currently works on records within the datatable's page)
- Notification for upload jobs

**Plans for improvements**
- Implement server-side pagination and filtering for all datatables
- Usage of [Laravel JSON:API](https://laraveljsonapi.io/) library that adheres to [json:api](https://jsonapi.org/) specifications: this should speed up the process of API creation and bring focus to configuration and designing the custom filters
- Usage of [pinia-jsonapi](https://github.com/mrichar1/pinia-jsonapi) on client-side to consume the standardized API endpoints
