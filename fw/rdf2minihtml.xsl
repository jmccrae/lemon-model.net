<?xml version="1.0"?>

<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">

  <xsl:strip-space elements="*" />
  <xsl:output method="xml" indent="yes" omit-xml-declaration="yes"/>

<!-- Modify this template for URIs specified in full with @rdf:resource -->  
<xsl:template name="display-uri">
    <xsl:param name="text"/>
    <xsl:choose>
      <xsl:when test="contains($text,'http://purl.org/olia/ubyPos.owl#')">
        <xsl:value-of select="'uby:'"/>
        <xsl:value-of select="substring-after($text,'http://purl.org/olia/ubyPos.owl#')"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$text"/>
      </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:variable name="apos">'</xsl:variable>
  
<xsl:template match="/rdf:RDF">  
  <xsl:for-each select="*[substring-after(@rdf:about,'#')='']">
    <xsl:call-template name="forprop2"/>
  </xsl:for-each>
</xsl:template>


<xsl:template name="forprop2">
  <table class="rdf">
     <xsl:if test="not(name()='rdf:Description')">
       <tr>
         <td> <a href="http://www.w3.org/1999/02/22-rdf-syntax-ns#type">rdf:type</a></td>
         <td> <a property="http://www.w3.org/1999/02/22-rdf-syntax-ns#type">
         <xsl:attribute name="href">
         <xsl:value-of select="concat(namespace-uri(),local-name())"/>
         </xsl:attribute>
         <xsl:value-of select="name()"/>
         </a></td>
       </tr>
     </xsl:if>
     <xsl:for-each select="*">
       <tr>
          <td> <a>
         <xsl:attribute name="href">
         <xsl:value-of select="concat(namespace-uri(),local-name())"/>
         </xsl:attribute>
         <xsl:value-of select="name()"/>
         </a>
         </td>
         <td>
            <xsl:choose>
              <xsl:when test="@rdf:resource">
                <xsl:variable name="rdfResource" select="@rdf:resource"/>
                <xsl:choose>
                <xsl:when test="not(substring-after(@rdf:resource,'#')='') and //*[@rdf:about=$rdfResource]">
                  <span>
                    <xsl:attribute name="id">
                      <xsl:value-of select="substring-after(@rdf:resource,'#')"/>
                    </xsl:attribute>
                    <xsl:attribute name="resource">
                      <xsl:value-of select="concat('#',substring-after(@rdf:resource,'#'))"/>
                    </xsl:attribute>
                    <xsl:attribute name="rel">
                      <xsl:value-of select="concat(namespace-uri(),local-name())"/>
                    </xsl:attribute>
                    <xsl:for-each select="//*[@rdf:about=$rdfResource]">
                      <xsl:call-template name="forprop2"/>
                    </xsl:for-each>
                  </span>
                </xsl:when>
                <xsl:otherwise>
                <a>
                 <xsl:attribute name="href">
                 <xsl:value-of select="@rdf:resource"/>
                 </xsl:attribute>
                 <xsl:attribute name="property">
                  <xsl:value-of select="concat(namespace-uri(),local-name())"/>
                 </xsl:attribute>
                 <xsl:call-template name="display-uri">
                   <xsl:with-param name="text" select="@rdf:resource"/>
                 </xsl:call-template>
                </a>
                <xsl:if test="not(starts-with(@rdf:resource,'http'))">
                  &#160;&#160;&#160;
                  <a class="load_entry"><xsl:attribute name="href">
                    <xsl:value-of select="concat('javascript:ajax_load_entry(',$apos,@rdf:resource,$apos,')')"/>
                  </xsl:attribute>More...</a>
                  <div style="display:none;">
                    <xsl:attribute name="id">
                      <xsl:value-of select="concat('la_',@rdf:resource)"/>
                    </xsl:attribute>
                  </div>
                </xsl:if>
                </xsl:otherwise>
                </xsl:choose>
              </xsl:when>
              <xsl:when test="node()[last()]/self::text()">
                 <xsl:if test="@xml:lang">
                 <img>
                   <xsl:attribute name="src">
                     <xsl:value-of select="concat('/img/flags/',@xml:lang,'.gif')"/>
                   </xsl:attribute>
                 </img>
                 </xsl:if>
                 &#x201c;<xsl:value-of select="node()"/>&#x201d;
              </xsl:when>
              <xsl:otherwise>
                <xsl:for-each select="*">
                  <xsl:call-template name="forprop2"/>
                </xsl:for-each>
              </xsl:otherwise>
            </xsl:choose>
         </td>
       </tr>
    </xsl:for-each>
  </table>
</xsl:template>

</xsl:stylesheet>
