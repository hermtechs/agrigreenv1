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


//get document from url
// const url1 = "content/Dr. Adnan Identification card 01-11-2022 (1).pdf";
const allPdfImageElements = document.querySelectorAll(".pdf-img");

//add images 
// console.log(allPdfImageElements)

//CHANGE CURRENT IMAGE/PDF PAGE
function changeImgUrl(){
  allPdfImageElements.forEach(pdfImage=>{
    const eachMainContainer = pdfImage.parentElement.parentElement.parentElement;
    // console.log(eachMainContainer);
    const img1 = eachMainContainer.querySelector('.pdf-img');
    const img1Url = img1.src;
    // console.log(img1Url)
    const nextImgBtn = eachMainContainer.querySelector('#next-page');
    // console.log(nextImgBtn);
    const prevImgBtn = eachMainContainer.querySelector('#prev-page');
    const pageNumberElement = eachMainContainer.querySelector('#page-num')

    const totalPages = eachMainContainer.querySelector("#page-count").innerText;
   
    //show next image/page;
    let pageNumber = pageNumberElement.innerText; //counter
    nextImgBtn.addEventListener('click', ()=>{
      if(pageNumber<=totalPages){
      if(pageNumber<totalPages){
        pageNumber++
      }
      // else{
      //   // alert('done')
      //   pageNumber=1;
      // }   
      // pageNumber++;
      let newImgSrc = img1.src.split(''); 
      // console.log(newImgSrc.length)
      if(pageNumber>=10){
        pageNumber = 1
        newImgSrc[newImgSrc.length-4]=pageNumber;
      }
      else{
        newImgSrc[newImgSrc.length-5]=pageNumber;

      }
      
      // console.log(newImgSrc);
     
      pageNumberElement.innerText = pageNumber;
    }
  })
   
  //show Previous image/page
  prevImgBtn.addEventListener('click', ()=>{
    if(pageNumber<=totalPages){
    if(pageNumber>1){
      pageNumber--;
    }  
    let newImgSrc = img1.src.split(''); 
    // console.log(newImgSrc.length)
    newImgSrc[newImgSrc.length-5]=pageNumber;
    // console.log(newImgSrc);
    newImgSrc = newImgSrc.join('');
    // console.log(newImgSrc)
    img1.src = newImgSrc;
    pageNumberElement.innerText = pageNumber;
  }
})

  })
}
changeImgUrl()