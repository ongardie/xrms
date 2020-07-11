# XRMS CRM

This is an unofficial mirror of the XRMS CVS repository located at
<http://xrms.cvs.sourceforge.net/>. The upstream CVS repository is read-only.
As of this writing in 2020, the last update to the project was in 2011.

This README describes this GitHub mirror of the XRMS code. The top-level
[README](../README) file describes the upstream project.

[XRMS](https://sourceforge.net/projects/xrms/) is a defunct Customer
Relationship Management system written in PHP for the
[LAMP stack](https://en.wikipedia.org/wiki/LAMP_(software_bundle)).

This mirror exists as a historical archive only. As one of the maintainers wrote:
> In all honesty, the XRMS source code is of historical interest only.
>
> XRMS was one of the most popular open source CRM systems in the early-mid
> 2000's. The rise of SugarCRM (semi-open) and Salesforce (closed), along
> with the nature of enterprise CRM applications, mean that a project of this
> nature isn't going to survive and thrive without a corporate sponsor and
> dedicated development resources.
>
> The code base and data model may be of use to you if you have an enterprise
> CRM problem and a fast food budget. Otherwise, examine the world of
> professionally supported applications.
>
> -- [Brian Peterson, 2016-04-17](https://sourceforge.net/p/xrms/support-requests/95/#68b5)

I (Diego) have a minor interest in keeping a copy of the code base available
online since I worked on it early in my career.

This mirror was created using these steps:

1. Download the CVS repository (350 MB):
    ```sh
    rsync -avi a.cvs.sourceforge.net::cvsroot/xrms/ xrms
    ```

2. Import it into git (95 MB):
    ```sh
    mkdir xrms-git
    cd xrms-git
    git cvsimport -d $(pwd)/../xrms xrms
    ```

3. Clean up the branch names:
    ```sh
    git branch -d origin
    git branch -m trunk
    ```

4. Push it to GitHub:
    ```sh
    git remote add github git@github.com:ongardie/xrms.git
    git push --mirror github
    ```

5. Set the default branch on GitHub to `trunk`.

6. Add this README.
