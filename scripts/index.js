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
// The workerSrc property shall be specified.
pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';
// console.log(mainPdfContainer);
const url = 'content/Dr. Adnan Identification card 01-11-2022 (1).pdf';
const url2 = "content/ملخص السيرة الذاتية للدكتور  عدنان بن فهد بن راشد الرمزاني النعيمي2022-11-01 (2).pdf"
const url3 = "content/Agrigreen foundation 2015& 2016 Activity Report (1).pdf"
const url4 = "content/[KIPA]SIF2020_entryform(_11.13).pdf"
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

//pdf3
const canvas3 = mainPdfContainer[2].querySelector('#pdf-render');
const ctx3 = canvas3.getContext('2d');

// pdf4
const canvas4 = mainPdfContainer[2].querySelector('#pdf-render');
const ctx4 = canvas3.getContext('2d');

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

    mainPdfContainer[0].querySelector('#page-count').textContent = pdfDoc.numPages;

    renderPage(pageNum);
  })
  .catch(err => {
    // Display error
    const div = document.createElement('div');
    div.className = 'error';
    div.appendChild(document.createTextNode(err.message));
    mainPdfContainer[0].querySelector('body').insertBefore(div, canvas1);
    // Remove top bar
    mainPdfContainer[0].querySelector('.top-bar').style.display = 'none';
  });

// Button Events
const prevBtn = mainPdfContainer[0].querySelector('#prev-page').addEventListener('click', showPrevPage)
const nextBtn = mainPdfContainer[0].querySelector('#next-page').addEventListener('click', showNextPage)


// PDF2
function renderPdf2(){
//render pdf 2
// Render the page
function renderPage (num){
pageIsRendering = true;

// Get page
pdfDoc.getPage(num).then(page => {
  // Set scale
  const viewport = page.getViewport({ scale });
  canvas2.height = viewport.height;
  canvas2.width = viewport.width;

  const renderCtx = {
    canvasContext: ctx2,
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
  mainPdfContainer[1].querySelector('#page-num').textContent = num;
});
};


pdfjsLib
  .getDocument(url2)
  .promise.then(pdfDoc_ => {
    pdfDoc = pdfDoc_;
    
    mainPdfContainer[1].querySelector('#page-count').textContent = pdfDoc.numPages;

    renderPage(pageNum);
  })
  .catch(err => {
    // Display error
    const div = document.createElement('div');
    div.className = 'error';
    div.appendChild(document.createTextNode(err.message));
    mainPdfContainer[1].querySelector('body').insertBefore(div, canvas2);
    // Remove top bar
    mainPdfContainer[1].querySelector('.top-bar').style.display = 'none';
  });
}

///PDF3
function renderPdf3(){
  //render pdf 3
// Render the page
function renderPage (num){
  pageIsRendering = true;
  
  // Get page
  pdfDoc.getPage(num).then(page => {
    // Set scale
    const viewport = page.getViewport({ scale });
    canvas3.height = viewport.height;
    canvas3.width = viewport.width;
  
    const renderCtx = {
      canvasContext: ctx3,
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
    mainPdfContainer[2].querySelector('#page-num').textContent = num;
  });
  };
  
  
  pdfjsLib
    .getDocument(url3)
    .promise.then(pdfDoc_ => {
      pdfDoc = pdfDoc_;
      
      mainPdfContainer[2].querySelector('#page-count').textContent = pdfDoc.numPages;
  
      renderPage(pageNum);
    })
    .catch(err => {
      // Display error
      const div = document.createElement('div');
      div.className = 'error';
      div.appendChild(document.createTextNode(err.message));
      mainPdfContainer[2].querySelector('body').insertBefore(div, canvas3);
      // Remove top bar
      mainPdfContainer[2].querySelector('.top-bar').style.display = 'none';
    });
  
}

function renderPdf4(){
   //render pdf 2
// Render the page
function renderPage (num){
  pageIsRendering = true;
  
  // Get page
  pdfDoc.getPage(num).then(page => {
    // Set scale
    const viewport = page.getViewport({ scale });
    canvas4.height = viewport.height;
    canvas4.width = viewport.width;
  
    const renderCtx = {
      canvasContext: ctx4,
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
    mainPdfContainer[3].querySelector('#page-num').textContent = num;
  });
  };
  
  
  pdfjsLib
    .getDocument(url4)
    .promise.then(pdfDoc_ => {
      pdfDoc = pdfDoc_;
      
      mainPdfContainer[3].querySelector('#page-count').textContent = pdfDoc.numPages;
  
      renderPage(pageNum);
    })
    .catch(err => {
      // Display error
      const div = document.createElement('div');
      div.className = 'error';
      div.appendChild(document.createTextNode(err.message));
      mainPdfContainer[3].querySelector('body').insertBefore(div, canvas4);
      // Remove top bar
      mainPdfContainer[3].querySelector('.top-bar').style.display = 'none';
    });
  
}


window.addEventListener('load', renderPdf2);
window.addEventListener('load', renderPdf3);
window.addEventListener('load', renderPdf4);




