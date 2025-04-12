# Setting Up the GitHub Wiki

To set up the Flopy CRM wiki on GitHub, follow these steps:

## 1. Enable the Wiki on GitHub

1. Go to the GitHub repository: https://github.com/aazzroy/flopy_crm
2. Click on the "Settings" tab
3. Scroll down to the "Features" section
4. Make sure the "Wikis" checkbox is enabled
5. Click "Save changes" if you made any changes

## 2. Create the Wiki Home Page

1. Go back to the repository main page
2. Click on the "Wiki" tab
3. Click the "Create the first page" button
4. Set the page title to "Home"
5. Copy and paste the content from `wiki/Home.md` into the editor
6. Add a commit message: "Add wiki home page"
7. Click "Save Page"

## 3. Add the Rest of the Wiki Pages

For each of the following files, create a new page with the corresponding title:

| File | Page Title |
|------|------------|
| `wiki/Installation-Guide.md` | Installation Guide |
| `wiki/User-Guide.md` | User Guide |
| `wiki/Developer-Guide.md` | Developer Guide |
| `wiki/API-Reference.md` | API Reference |
| `wiki/Database-Schema.md` | Database Schema |
| `wiki/URL-Routing.md` | URL Routing |
| `wiki/Contributing-Guide.md` | Contributing Guide |

For each page:
1. Click "New Page"
2. Enter the page title as shown in the table
3. Copy and paste the content from the corresponding file
4. Add a commit message: "Add [Page Title] page"
5. Click "Save Page"

## 4. Set Up the Sidebar

1. Create a new page named "_Sidebar"
2. Add the following content:

```markdown
### Flopy CRM Wiki

- [Home](Home)
- [Installation Guide](Installation-Guide)
- [User Guide](User-Guide)
- [Developer Guide](Developer-Guide)
- [API Reference](API-Reference)
- [Database Schema](Database-Schema)
- [URL Routing](URL-Routing)
- [Contributing Guide](Contributing-Guide)
```

3. Add a commit message: "Add wiki sidebar"
4. Click "Save Page"

## 5. Verify All Links

1. Visit each page and verify that all internal links are working correctly
2. Fix any broken links if needed

Your GitHub wiki is now set up and ready to use! 