<h1>☁️ DOCUMENTS</h1>


<table style='width: 100%; text-align: left;'>
  <tr>
    <td>
      <a href="javascript:void(0)" id="createNewDocumentButton" class="button" onclick="createDocument()">📄 CREATE NEW DOCUMENT</a>
      <br><br>
      <div id="structureWithFoldersAndDocuments"></div>
    </td>
    <td>
      <?php
      // Check if the 'CurrentDocument' cookie is set
      $showDocument = isset($_COOKIE['CurrentDocument']);
      ?>
      <div style="display: <?php echo $showDocument ? 'block' : 'none'; ?>">
        <input type="hidden" id="idpk" value=""> <!-- hidden input field to communicate the idpk of the current document, value is set using JS and a cookie -->
        <input type="text" id="title" placeholder="SomeFolder/AnotherFolder/DocumentName">
        <a href="javascript:void(0)" id="removeDocumentButton" class="button" onclick="confirmDeletion()" style="opacity: 0.2; display: block; text-align: right;">❌ REMOVE</a>
        <br>
        <textarea id="editor"></textarea>
      </div>
    </td>
  </tr>
</table>









<link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">
<!-- <link href="https://cdn.jsdelivr.net/npm/suneditor@latest/assets/css/suneditor.css" rel="stylesheet"> -->
<!-- <link href="https://cdn.jsdelivr.net/npm/suneditor@latest/assets/css/suneditor-contents.css" rel="stylesheet"> -->



<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js"></script>
<!-- languages (Basic Language: English/en) -->
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/src/lang/en.js"></script>


<!-- for math -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex/dist/contrib/auto-render.min.js"></script>






<script>
  // Get the input elements
  const titleField = document.getElementById('title');
  const idpkField = document.getElementById('idpk');

  /**
   * ID : 'suneditor_editor'
   * ClassName : 'sun-editor'
   */
  // ID or DOM object
  const editor = SUNEDITOR.create((document.getElementById('editor') || 'editor'), {
    // Language global object (default: en)
    lang: SUNEDITOR_LANG['en'],
    minHeight: '500px',
    height: 'auto',
    buttonList: [
      ['undo', 'redo'],
      ['font', 'fontSize', 'formatBlock'],
      ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
      ['fontColor', 'hiliteColor', 'textStyle'],
      ['removeFormat'],
      ['outdent', 'indent'],
      ['align', 'horizontalRule', 'list', 'lineHeight'],
      // ['table', 'blockquote', 'link', 'image', 'video', 'audio'],
      ['table', 'blockquote', 'link', 'image'], 
      ['math'], // Include the math button
      // ['template', 'showBlocks', 'codeView'],
      ['showBlocks', 'codeView'],
      ['preview', 'print'],
      ['save'],
      ['fullScreen']
    ],
    lineHeights: [
      { text: '1', value: 1 }, // Default line height
      { text: '1.5', value: 1.5 },
      { text: '2', value: 2 },
      { text: '2.5', value: 2.5 },
      { text: '3', value: 3 }
    ],
    defaultStyle: 'font-family: Arial; font-size: 12px; line-height: 1; text-align: left;', // Set the default text settings
    // placeholder: 'enlarge this editor by clicking on the icon with the two arrows in the top left corner - - - you can name this document by entering a title in the field above - - - if you enter for example - SomeFolder/AnotherFolder/DocumentName -, the document will be placed in the corresponding folder structure, which will then appear on the left hand side, next to this editor', // Set placeholder text
    placeholder: 'enlarge this editor by clicking on the icon with the two arrows in the top left corner', // Set placeholder text

    // Math options (integrate KaTeX rendering)
    katex: katex,
    callBackSave: function (contents) {
      saveDocument(contents);
    }
  });


















  









  // Function to list all documents
  function listDocuments() {
    const idpk = document.getElementById('idpk').value;
    const currentDocumentId = getCookie('CurrentDocument'); // Get the current document ID from the cookie

    fetch('SaveDataDocuments.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'list',
        idpk: idpk // idpk of the document currently opened
      })
    })
      .then(response => response.json())
      .then(data => {
        const container = document.getElementById('structureWithFoldersAndDocuments');
        container.innerHTML = ''; // Clear any existing content

        if (data.status === 'success') {
          // Process the data
          const folderStructure = {};

          data.data.forEach(doc => {
            const parts = doc.title.split('/');
            let currentLevel = folderStructure;

            // Build nested folder structure
            parts.forEach((part, index) => {
              if (index === parts.length - 1) {
                // Last part is the document name
                if (!currentLevel.documents) currentLevel.documents = [];
                currentLevel.documents.push({ name: part, idpk: doc.idpk });
              } else {
                // Folder part
                if (!currentLevel[part]) currentLevel[part] = {};
                currentLevel = currentLevel[part];
              }
            });
          });

          // Find the path to the current document
          function findDocumentPath(structure, targetId, path = []) {
            for (const key in structure) {
              if (key === 'documents') {
                const doc = structure.documents.find(doc => String(doc.idpk) === String(targetId));
                if (doc) return path;
              } else {
                const result = findDocumentPath(structure[key], targetId, [...path, key]);
                if (result) return result;
              }
            }
            return null;
          }

          const pathToCurrent = findDocumentPath(folderStructure, currentDocumentId);

          // Helper function to create HTML structure recursively
          function createStructureHTML(structure, container, isRoot = false) {
            const folders = Object.keys(structure)
              .filter(key => key !== 'documents')
              .sort((a, b) => a.localeCompare(b));
            const documents = structure.documents ? structure.documents.sort((a, b) => a.name.localeCompare(b.name)) : [];

            // Render folders first
            folders.forEach(folder => {
              const folderElement = document.createElement('div');
              folderElement.style.marginLeft = isRoot ? '0px' : '20px';

              // Folder link
              const folderLink = document.createElement('a');
              folderLink.href = '#';
              folderLink.textContent = `📁 ${folder}`;

              // Folder toggle functionality
              const subContainer = document.createElement('div');
              subContainer.style.marginLeft = '20px';
              subContainer.style.display = 'none'; // Hidden by default

              folderLink.addEventListener('click', (event) => {
                event.preventDefault();
                if (subContainer.style.display === 'none') {
                  subContainer.style.display = 'block';
                  folderLink.textContent = `📂 ${folder}`;
                } else {
                  subContainer.style.display = 'none';
                  folderLink.textContent = `📁 ${folder}`;
                }
              });

              folderElement.appendChild(folderLink);
              container.appendChild(folderElement);
              container.appendChild(subContainer);

              createStructureHTML(structure[folder], subContainer);

              // Automatically open folders if they are in the path to the current document
              if (pathToCurrent && pathToCurrent.includes(folder)) {
                subContainer.style.display = 'block';
                folderLink.textContent = `📂 ${folder}`;
              }
            });

            // Render documents
            documents.forEach(doc => {
              const docElement = document.createElement('div');
              docElement.style.marginLeft = isRoot ? '0px' : '20px';

              // Document link
              const docLink = document.createElement('a');
              docLink.href = '#';
              // docLink.textContent = `📄 ${doc.name} (${doc.idpk})`;
              docLink.textContent = `📄 ${doc.name}`;

              if (String(doc.idpk) === String(currentDocumentId)) {
                docLink.style.backgroundColor = '#505050';
              }

              docLink.addEventListener('click', (event) => {
                event.preventDefault();
                console.log(`document opened: ${doc.name} with idpk: ${doc.idpk}`);

                // Set cookie for 10 years
                const expiryDate = new Date();
                expiryDate.setFullYear(expiryDate.getFullYear() + 10);
                document.cookie = `CurrentDocument=${doc.idpk}; expires=${expiryDate.toUTCString()}; path=/`;

                // Reload the current page
                location.reload();
              });

              docElement.appendChild(docLink);
              container.appendChild(docElement);
            });
          }

          // Generate and append the structured HTML
          createStructureHTML(folderStructure, container, true);
        }
      })
      .catch(error => {
        console.error('Error listing documents:', error);
      });
  }
















  


  // Function to create a new document
  function createDocument() {
    const idpk = "NotDefinedYet";
    const title = "";
    const content = ""; // Use an empty string or default content for a new document

    fetch('SaveDataDocuments.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'create', // Use 'create' to indicate document creation
        idpk: idpk,
        title: title,
        content: content
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        console.log('Document created successfully!');
        // Reload the list of documents
        // listDocuments();

        // Assuming the response data contains the new document's idpk
        const newDocId = data.idpk;

        // Set cookie for 10 years
        const expiryDate = new Date();
        expiryDate.setFullYear(expiryDate.getFullYear() + 10);
        document.cookie = `CurrentDocument=${newDocId}; expires=${expiryDate.toUTCString()}; path=/`;

        // Reload the page after removing the document
        location.reload();
      } else {
        console.error('Error creating document:', data.message);
      }
    })
    .catch(error => {
      console.error('Error creating document:', error);
    });
  }












  // Function to confirm deletion
  function confirmDeletion() {
    const userConfirmed = confirm("Are you sure you want to remove this document?");
    if (userConfirmed) {
        removeDocument();
    } else {
        console.log("Document removal cancelled.");
    }
  }
  
  // Function to remove the document
  function removeDocument() {
    const idpk = document.getElementById('idpk').value;
    const title = document.getElementById('title').value;
    const content = ""; // Use an empty string or default content for a new document

    fetch('SaveDataDocuments.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'remove',
        idpk: idpk,
        title: title,
        content: content
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        // alert('Document removed successfully!');
        // Reload the list of documents
        // listDocuments();

        // Reset the cookie
        document.cookie = "CurrentDocument=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
        // Reload the page after removing the document
        location.reload();
      } else {
        // alert(data.message);
      }
    })
    .catch(error => {
      console.error('Error removing document:', error);
    });
  }














  // Function to load the document
  function loadDocument() {
    const idpk = document.getElementById('idpk').value;

    fetch('SaveDataDocuments.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'load',
        idpk: idpk
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        document.getElementById('title').value = data.data.title;
        editor.setContents(data.data.content);
      } else {
        // alert(data.message);
      }
    })
    .catch(error => {
      console.error('Error loading document:', error);
    });
  }














  // Function to save the document
  function saveDocument(contents) {
    const idpk = document.getElementById('idpk').value;
    const title = document.getElementById('title').value;

    fetch('SaveDataDocuments.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'save',
        idpk: idpk,
        title: title,
        content: contents
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        // alert('Document saved successfully!');
      } else {
        // alert(data.message);
      }
    })
    .catch(error => {
      console.error('Error saving document:', error);
    });
  }

  // Function to save the title
  function saveTitle() {
    const idpk = document.getElementById('idpk').value;
    const title = document.getElementById('title').value;
    const content = "";

    fetch('SaveDataDocuments.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams({
        action: 'saveTitle',
        idpk: idpk,
        title: title,
        content: content
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        // alert('Document saved successfully!');
      } else {
        // alert(data.message);
      }
    })
    .catch(error => {
      console.error('Error saving document:', error);
    });
  }









  // Save title when the user leaves the title field
  titleField.addEventListener('blur', () => {
    saveTitle();
  });
  
  // Save content every time it changes
  editor.onChange = function (contents) {
    saveDocument(contents);
  };


  











  window.onload = function () {
    loadDocument();
    listDocuments();
  };

  // Function to get the value of a cookie by name
  function getCookie(name) {
      let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
      if (match) return match[2];
  }

  // Set the value of the hidden input field
  document.getElementById('idpk').value = getCookie('CurrentDocument');
</script>

