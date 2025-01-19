<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
async function generatePDF(cartId, action, type) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    const pageHeight = doc.internal.pageSize.height; // Get PDF page height
    const marginTop = 10; // Top margin
    const marginBottom = 10; // Bottom margin
    const usablePageHeight = pageHeight - marginTop - marginBottom;

    // Titles
    const invoiceTitleDiv = document.getElementById("DivContentToPDFTitleInvoice");
    const deliveryReceiptTitleDiv = document.getElementById("DivContentToPDFTitleDeliveryReceipt");

    const contentToPDFAdditionalInformationForInvoicesDiv = document.getElementById("DivContentToPDFAdditionalInformationForInvoices");
    const contentToPDFAdditionalInformationAboutTheOwnCompanyDiv = document.getElementById("DivContentToPDFAdditionalInformationAboutTheOwnCompany");

    // Helper function to render a div into the PDF
    async function renderDivToPDF(divId, yOffset) {
        const contentDiv = document.getElementById(divId);
        if (contentDiv) {
            const canvas = await html2canvas(contentDiv, {
                scale: 2, // Increase the scale for better resolution
                backgroundColor: "#ffffff", // Force white background
                onclone: (clonedDocument) => {
                    // Modify the cloned document for black text and white background
                    const clonedContentDiv = clonedDocument.getElementById(divId);
                    if (clonedContentDiv) {
                        clonedContentDiv.querySelectorAll("*").forEach((element) => {
                            element.style.color = "#000000"; // Black text
                            element.style.backgroundColor = "transparent"; // Transparent backgrounds (if needed)
                        });

                        // Hide <td> and <span> elements with class 'DontDisplayInPDF'
                        clonedContentDiv.querySelectorAll("td.DontDisplayInPDF, span.DontDisplayInPDF").forEach((element) => {
                            element.style.display = "none";
                        });

                        // Conditionally hide <td> and <span> elements with class 'DontDisplayInPDFInDeliveryReceipt'
                        if (type === "deliveryReceipt") {
                            clonedContentDiv.querySelectorAll("td.DontDisplayInPDFInDeliveryReceipt, span.DontDisplayInPDFInDeliveryReceipt").forEach((element) => {
                                element.style.display = "none";
                            });
                        }

                        // show <td> and <span> elements with class 'OnlyDisplayInPDF'
                        clonedContentDiv.querySelectorAll("td.OnlyDisplayInPDF, span.OnlyDisplayInPDF").forEach((element) => {
                            element.style.display = "block";
                        });

                        // Conditionally show <td> and <span> elements with class 'OnlyDisplayInPDFInInvoice'
                        if (type === "invoice") {
                            clonedContentDiv.querySelectorAll("td.OnlyDisplayInPDFInInvoice, span.OnlyDisplayInPDFInInvoice").forEach((element) => {
                                element.style.display = "block";
                            });
                        }
                    }
                }
            });
            const imgData = canvas.toDataURL("image/png");

            // Calculate width and height to fit the PDF page
            const imgWidth = 190; // Adjust if needed
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            if (yOffset + imgHeight > pageHeight - marginBottom) {
                doc.addPage(); // Add a new page
                yOffset = marginTop; // Reset yOffset for the new page
            }

            doc.addImage(imgData, "PNG", 10, yOffset, imgWidth, imgHeight);
            return imgHeight; // Return how tall that image ended up
        } else {
            console.error(`Div with ID '${divId}' not found.`);
            return 0;
        }
    }

    // Start rendering from the top with a bit of distance
    let currentYOffset = marginTop;

    // 1) Toggle display for the correct title
    if (type === 'invoice') {
        // Make the Invoice title visible so it can be captured
        invoiceTitleDiv.style.display = "block";
        // Render the invoice title
        currentYOffset += await renderDivToPDF("DivContentToPDFTitleInvoice", currentYOffset);
        // Hide it again after capturing
        invoiceTitleDiv.style.display = "none";
    } else {
        // Make the Delivery Receipt title visible so it can be captured
        deliveryReceiptTitleDiv.style.display = "block";
        // Render the delivery receipt title
        currentYOffset += await renderDivToPDF("DivContentToPDFTitleDeliveryReceipt", currentYOffset);
        // Hide it again after capturing
        deliveryReceiptTitleDiv.style.display = "none";
    }

    // Add some spacing
    currentYOffset += 10;

    // 2) Render main PDF content
    currentYOffset += await renderDivToPDF("DivContentToPDFCart", currentYOffset);
    currentYOffset += 10;
    currentYOffset += await renderDivToPDF("DivContentToPDFAssociatedTransactions", currentYOffset);
    currentYOffset += 20;
    // Conditionally render additional information for invoices
    if (type === "invoice") {
        contentToPDFAdditionalInformationForInvoicesDiv.style.display = "block";
        currentYOffset += await renderDivToPDF("DivContentToPDFAdditionalInformationForInvoices", currentYOffset);
        contentToPDFAdditionalInformationForInvoicesDiv.style.display = "none";
        currentYOffset += 10;
    }
    contentToPDFAdditionalInformationAboutTheOwnCompanyDiv.style.display = "block";
    currentYOffset += await renderDivToPDF("DivContentToPDFAdditionalInformationAboutTheOwnCompany", currentYOffset);
    contentToPDFAdditionalInformationAboutTheOwnCompanyDiv.style.display = "none";

    // Choose your file name based on type
    const title = (type === 'invoice') ? 'Invoice' : 'DeliveryReceipt';



























    
    // 3) Execute the action (download, email, or print)
    if (action === "download") {
        // Trigger a direct download
        doc.save(`TRAMANNPORT${title}ForCart${cartId}.pdf`);
    // } else if (action === "email") {
// 
// 
// 
// 
// 
    //     // Convert PDF to Blob
    //     const pdfBlob = doc.output("blob");
// 
    //     // Translate the title dynamically
    //     const translatedTitle = (type === 'invoice') ? 'invoice' : 'delivery receipt';
// 
    //     // Use PHP variables in JS by echoing them into JavaScript
    //     const userFirstName = "<?php // echo $user['FirstName']; ?>";
    //     const userLastName = "<?php // echo $user['LastName']; ?>";
    //     const userIdPk = "<?php // echo $user['idpk']; ?>";
    //     const userCompanyName = "<?php // echo $user['CompanyName']; ?>";
    //     const explorerOrCreator = "<?php // echo $user['ExplorerOrCreator']; ?>";
// 
    //     // Determine sender name based on PHP variable
    //     const senderName = explorerOrCreator == 0 
    //         ? `${userFirstName} ${userLastName} (${userIdPk})`
    //         : `${userCompanyName} (${userIdPk})`;
// 
    //     // Prepare email subject and body dynamically
    //     const emailSubject = encodeURIComponent(`TRAMANN PORT - ${translatedTitle} for cart ${cartId} from ${senderName}`);
    //     const emailBody = encodeURIComponent(`Hi,
// 
    //     Please find the ${translatedTitle} for cart ${cartId} attached.
// 
    //     Sincerely yours,
    //     ${senderName}`);
// 
    //     // Open the default email client using mailto
    //     const mailtoLink = `mailto:?subject=${emailSubject}&body=${emailBody}`;
    //     window.location.href = mailtoLink;
// 
// 
// 
// 
// 
    // } else if (action === "print") {
    //     // Open the PDF in a new window for printing
    //     const pdfDataUri = doc.output("dataurlstring");
    //     const printWindow = window.open("", "_blank");
// 
    //     if (printWindow) {
    //         printWindow.document.write(
    //             `<iframe width='100%' height='100%' src='${pdfDataUri}' frameborder='0'></iframe>`
    //         );
    //     } else {
    //         console.error("Failed to open print window.");
    //     }
    }
}
</script>
