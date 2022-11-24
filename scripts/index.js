const navBtn = document.querySelector('.humburger');
const smallScreenMenu = document.querySelector('.nav-links-small-screen')
// console.log(navBtn)
navBtn.addEventListener('click', ()=>{
    if(navBtn.classList.contains('nav-open')){
        navBtn.classList.remove('nav-open');
        smallScreenMenu.style.transform = 'translateX(-100%)'
    }
    else{
        navBtn.classList.add('nav-open');
        smallScreenMenu.style.transform = 'translateX(0%)'
    }
})
const navLinksSmallScreen = document.querySelectorAll(".nav-links-small-screen a");
navLinksSmallScreen.forEach(link=>{
    link.addEventListener('click', ()=>{
        smallScreenMenu.style.transform = 'translateX(-100%)'
    })
})

//pdf viewer
const mainPdfContainer =  document.querySelectorAll('.main-pdf-container');
// console.log(mainPdfContainer);
const url = 'content/Dr. Adnan Identification card 01-11-2022 (1).pdf';
const url2 = "content/ملخص السيرة الذاتية للدكتور  عدنان بن فهد بن راشد الرمزاني النعيمي2022-11-01 (2).pdf"


let pdfDoc = null,
  pageNum = 1,
  pageIsRendering = false,
  pageNumIsPending = null;

const scale = 1.5,
  canvas1 = mainPdfContainer[0].querySelector('#pdf-render'),
  ctx = canvas1.getContext('2d');

// Get Document
const canvas2 = mainPdfContainer[1].querySelector('#pdf-render');
const ctx2 = canvas2.getContext('2d');
// Render the page
function renderPage (num){
  pageIsRendering = true;

  // Get page
  pdfDoc.getPage(num).then(page => {
    // Set scale
    const viewport = page.getViewport({ scale });
    canvas1.height = viewport.height;
    canvas1.width = viewport.width;

    const renderCtx = {
      canvasContext: ctx,
      viewport
    };

    page.render(renderCtx).promise.then(() => {
      pageIsRendering = false;

      if (pageNumIsPending !== null) {
        renderPage(pageNumIsPending);
        pageNumIsPending = null;
      }
    });

    // Output current page
    mainPdfContainer[0].querySelector('#page-num').textContent = num;
  });
};

// // Check for pages rendering
// function queueRenderPage (num){
//   if (pageIsRendering) {
//     pageNumIsPending = num;
//   } else {
//     renderPage(num);
//   }
// };

// // Show Prev Page
// const showPrevPage = () => {
//   if (pageNum <= 1) {
//     return;
//   }
//   pageNum--;
//   queueRenderPage(pageNum);
// };

// // Show Next Page
// function showNextPage(){
//   if (pageNum >= pdfDoc.numPages) {
//     return;
//   }
//   pageNum++;
//   queueRenderPage(pageNum);
// };

// // Get Document
// pdfjsLib
//   .getDocument(url)
//   .promise.then(pdfDoc_ => {
//     pdfDoc = pdfDoc_;

//     mainPdfContainer[0].querySelector('#page-count').textContent = pdfDoc.numPages;

//     renderPage(pageNum);
//   })
//   .catch(err => {
//     // Display error
//     const div = document.createElement('div');
//     div.className = 'error';
//     div.appendChild(document.createTextNode(err.message));
//     mainPdfContainer[0].querySelector('body').insertBefore(div, canvas1);
//     // Remove top bar
//     mainPdfContainer[0].querySelector('.top-bar').style.display = 'none';
//   });

// // Button Events
// const prevBtn = document.querySelectorAll('#prev-page')
// const nextBtn = document.querySelectorAll('#next-page')

// prevBtn.forEach(btn=>btn.addEventListener('click', showPrevPage));

// nextBtn.forEach(btn=>btn.addEventListener('click', showNextPage))
// //render pdf 2


// // Render the page
// function renderPage (num){
// pageIsRendering = true;

// // Get page
// pdfDoc.getPage(num).then(page => {
//   // Set scale
//   const viewport = page.getViewport({ scale });
//   canvas2.height = viewport.height;
//   canvas2.width = viewport.width;

//   const renderCtx = {
//     canvasContext: ctx2,
//     viewport
//   };

//   page.render(renderCtx).promise.then(() => {
//     pageIsRendering = false;

//     if (pageNumIsPending !== null) {
//       renderPage(pageNumIsPending);
//       pageNumIsPending = null;
//     }
//   });

//   // Output current page
//   mainPdfContainer[1].querySelector('#page-num').textContent = num;
// });
// };

// pdfjsLib
//   .getDocument(url2)
//   .promise.then(pdfDoc_ => {
//     pdfDoc = pdfDoc_;

//     mainPdfContainer[1].querySelector('#page-count').textContent = pdfDoc.numPages;

//     renderPage(pageNum);
//   })
//   .catch(err => {
//     // Display error
//     const div = document.createElement('div');
//     div.className = 'error';
//     div.appendChild(document.createTextNode(err.message));
//     mainPdfContainer[1].querySelector('body').insertBefore(div, canvas2);
//     // Remove top bar
//     mainPdfContainer[1].querySelector('.top-bar').style.display = 'none';
//   });