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
const url = 'content/Dr. Adnan Identification card 01-11-2022 (1).pdf';

let pdfDoc = null,
  pageNum = 1,
  pageIsRendering = false,
  pageNumIsPending = null;

const scale = 1.5,
  canvas = document.querySelector('#pdf-render'),
  ctx = canvas.getContext('2d');

// Render the page
function renderPage (num){
  pageIsRendering = true;

  // Get page
  pdfDoc.getPage(num).then(page => {
    // Set scale
    const viewport = page.getViewport({ scale });
    canvas.height = viewport.height;
    canvas.width = viewport.width;

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
    document.querySelector('#page-num').textContent = num;
  });
};

// Check for pages rendering
function queueRenderPage (num){
  if (pageIsRendering) {
    pageNumIsPending = num;
  } else {
    renderPage(num);
  }
};

// Show Prev Page
const showPrevPage = () => {
  if (pageNum <= 1) {
    return;
  }
  pageNum--;
  queueRenderPage(pageNum);
};

// Show Next Page
function showNextPage(){
  if (pageNum >= pdfDoc.numPages) {
    return;
  }
  pageNum++;
  queueRenderPage(pageNum);
};

// Get Document
pdfjsLib
  .getDocument(url)
  .promise.then(pdfDoc_ => {
    pdfDoc = pdfDoc_;

    document.querySelector('#page-count').textContent = pdfDoc.numPages;

    renderPage(pageNum);
  })
  .catch(err => {
    // Display error
    const div = document.createElement('div');
    div.className = 'error';
    div.appendChild(document.createTextNode(err.message));
    document.querySelector('body').insertBefore(div, canvas);
    // Remove top bar
    document.querySelector('.top-bar').style.display = 'none';
  });

// Button Events
document.querySelector('#prev-page').addEventListener('click', showPrevPage);
document.querySelector('#next-page').addEventListener('click', showNextPage);
